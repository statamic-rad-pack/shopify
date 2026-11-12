<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use StatamicRadPack\Shopify\Activity\ActivityStatus;
use StatamicRadPack\Shopify\Events;

/**
 * A refund removes its line items from the products' purchase history, on the
 * date of the refund, and re-imports them to pick up any restocked inventory.
 *
 * Shopify records the items of a cancelled order as a refund too, so this also
 * covers most cancellations (see OrderCancelledController).
 */
class RefundCreateController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        $data = json_decode($request->getContent());

        $lineItems = collect($data->refund_line_items ?? [])
            ->map(fn ($refunded) => (object) [
                'product_id' => $refunded->line_item->product_id ?? null,
                'variant_id' => $refunded->line_item->variant_id ?? null,
                'sku' => $refunded->line_item->sku ?? null,
                'title' => $refunded->line_item->title ?? null,
                'quantity' => $refunded->quantity ?? 0,
            ]);

        $queued = $this->queueLineItemImports($request, $lineItems, Carbon::parse($data->created_at ?? null), 'refunds/create', -1);

        $this->recordOutcome($request, $queued ? ActivityStatus::Updated : ActivityStatus::Skipped, [
            'resource_title' => isset($data->order_id) ? "Refund for order {$data->order_id}" : null,
            'message' => $queued ? "Removed {$queued} refunded product(s) from purchase history" : 'No Shopify products refunded',
        ]);

        Events\RefundCreate::dispatch($data);

        return response()->json([], 200);
    }
}
