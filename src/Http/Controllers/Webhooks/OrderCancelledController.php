<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use StatamicRadPack\Shopify\Activity\ActivityStatus;
use StatamicRadPack\Shopify\Events;

/**
 * A cancelled order removes its line items from the products' purchase history,
 * on the date it was cancelled.
 *
 * Cancelling usually creates a refund for the items, which RefundCreateController
 * handles. The cancelled order includes its refunds, so only the quantities they
 * don't already cover are removed here, and nothing is removed twice.
 */
class OrderCancelledController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        $data = json_decode($request->getContent());

        $refunded = [];

        foreach ($data->refunds ?? [] as $refund) {
            foreach ($refund->refund_line_items ?? [] as $item) {
                $lineItemId = (string) ($item->line_item_id ?? $item->line_item->id ?? '');
                $refunded[$lineItemId] = ($refunded[$lineItemId] ?? 0) + ($item->quantity ?? 0);
            }
        }

        $lineItems = collect($data->line_items ?? [])
            ->map(function ($item) use ($refunded) {
                $item = clone $item;
                $item->quantity = ($item->quantity ?? 1) - ($refunded[(string) ($item->id ?? '')] ?? 0);

                return $item;
            });

        $queued = $this->queueLineItemImports($request, $lineItems, Carbon::parse($data->cancelled_at ?? null), 'orders/cancelled', -1);

        $this->recordOutcome($request, $queued ? ActivityStatus::Updated : ActivityStatus::Skipped, [
            'message' => $queued
                ? "Removed {$queued} cancelled product(s) from purchase history"
                : 'No Shopify products to remove (refunds cover the cancelled items)',
        ]);

        Events\OrderCancelled::dispatch($data);

        return response()->json([], 200);
    }
}
