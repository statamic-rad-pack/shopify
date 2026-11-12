<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use StatamicRadPack\Shopify\Activity\ActivityStatus;
use StatamicRadPack\Shopify\Events;

class OrderCreateController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        // Decode data
        $data = json_decode($request->getContent());

        $queued = $this->queueLineItemImports($request, $data->line_items ?? [], Carbon::parse($data->created_at ?? null), 'orders/create');

        $this->recordOutcome($request, $queued ? ActivityStatus::Updated : ActivityStatus::Skipped, [
            'message' => $queued ? "Queued {$queued} product update(s) for purchase history and stock" : 'No Shopify products in this order',
        ]);

        Events\OrderCreate::dispatch($data);

        return response()->json([], 200);
    }
}
