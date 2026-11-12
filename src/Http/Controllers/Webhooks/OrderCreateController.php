<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Activity\ActivityStatus;
use StatamicRadPack\Shopify\Events;

class OrderCreateController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        // Decode data
        $data = json_decode($request->getContent());

        $storeHandle = $request->attributes->get('shopify_store_handle');
        $date = Carbon::parse($data->created_at ?? null);

        // One job per product (not per line item), skipping custom/deleted products.
        // Quantities are keyed by variant id where available, falling back to SKU.
        $queued = collect($data->line_items ?? [])
            ->filter(fn ($item) => ! empty($item->product_id))
            ->groupBy(fn ($item) => $item->product_id)
            ->each(function ($items, $productId) use ($date, $storeHandle, $request) {
                $quantities = [];

                foreach ($items as $item) {
                    $key = (string) ($item->variant_id ?? $item->sku ?? '');
                    $quantities[$key] = ($quantities[$key] ?? 0) + ($item->quantity ?? 1);
                }

                // each product gets its own activity, linked to this order delivery
                ActivityLog::queueProductImport(
                    (int) $productId,
                    $storeHandle,
                    Activity::SOURCE_WEBHOOK,
                    orderData: [
                        'quantity' => $quantities,
                        'date' => $date,
                    ],
                    attributes: [
                        'topic' => 'orders/create',
                        'parent_id' => $this->activityId($request),
                        'resource_title' => $items->first()->title ?? null,
                    ],
                );
            })
            ->count();

        $this->recordOutcome($request, $queued ? ActivityStatus::Updated : ActivityStatus::Skipped, [
            'message' => $queued ? "Queued {$queued} product update(s) for purchase history and stock" : 'No Shopify products in this order',
        ]);

        Events\OrderCreate::dispatch($data);

        return response()->json([], 200);
    }
}
