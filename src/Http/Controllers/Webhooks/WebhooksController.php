<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Statamic\Http\Controllers\Controller;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Activity\ActivityStatus;

class WebhooksController extends Controller
{
    /**
     * The activity log entry the webhook middleware recorded for this delivery.
     */
    protected function activityId(Request $request): ?string
    {
        return $request->attributes->get('shopify_activity_id');
    }

    protected function storeHandle(Request $request): ?string
    {
        return $request->attributes->get('shopify_store_handle');
    }

    /**
     * Record the outcome of a webhook handled without queueing an import.
     */
    protected function recordOutcome(Request $request, ActivityStatus $status, array $attributes = []): void
    {
        ActivityLog::update($this->activityId($request), ['status' => $status->value] + $attributes);
    }

    /**
     * Re-import the products in an order's line items, adjusting their purchase
     * history by each line's quantity: added for orders, removed (a negative
     * $direction) for cancellations and refunds. Re-importing also refreshes stock.
     *
     * One job per product (not per line item), skipping custom and deleted products.
     * Quantities are keyed by variant id where available, falling back to SKU.
     *
     * @param  iterable<object>  $lineItems  with product_id, variant_id, sku, quantity and title
     * @return int the number of products queued
     */
    protected function queueLineItemImports(Request $request, iterable $lineItems, Carbon $date, string $topic, int $direction = 1): int
    {
        return collect($lineItems)
            ->filter(fn ($item) => ! empty($item->product_id) && ($item->quantity ?? 1) > 0)
            ->groupBy(fn ($item) => $item->product_id)
            ->each(function ($items, $productId) use ($request, $date, $topic, $direction) {
                $quantities = [];

                foreach ($items as $item) {
                    $key = (string) ($item->variant_id ?? $item->sku ?? '');
                    $quantities[$key] = ($quantities[$key] ?? 0) + ($item->quantity ?? 1) * $direction;
                }

                // each product gets its own activity, linked to this delivery
                ActivityLog::queueProductImport(
                    (int) $productId,
                    $this->storeHandle($request),
                    Activity::SOURCE_WEBHOOK,
                    orderData: [
                        'quantity' => $quantities,
                        'date' => $date,
                    ],
                    attributes: [
                        'topic' => $topic,
                        'parent_id' => $this->activityId($request),
                        'resource_title' => $items->first()->title ?? null,
                    ],
                );
            })
            ->count();
    }
}
