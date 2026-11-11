<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use StatamicRadPack\Shopify\Events;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;

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
        collect($data->line_items ?? [])
            ->filter(fn ($item) => ! empty($item->product_id))
            ->groupBy(fn ($item) => $item->product_id)
            ->each(function ($items, $productId) use ($date, $storeHandle) {
                $quantities = [];

                foreach ($items as $item) {
                    $key = (string) ($item->variant_id ?? $item->sku ?? '');
                    $quantities[$key] = ($quantities[$key] ?? 0) + ($item->quantity ?? 1);
                }

                ImportSingleProductJob::dispatch(
                    (int) $productId,
                    [
                        'quantity' => $quantities,
                        'date' => $date,
                    ],
                    $storeHandle
                );
            });

        Events\OrderCreate::dispatch($data);

        return response()->json([], 200);
    }
}
