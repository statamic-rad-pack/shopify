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

        foreach ($data->line_items as $item) {
            ImportSingleProductJob::dispatch(
                $item->product_id,
                [
                    'quantity' => [$item->sku => $item->quantity ?? 1],
                    'date' => Carbon::parse($data->created_at),
                ]
            );
        }

        Events\OrderCreate::dispatch($data);

        return response()->json([], 200);
    }
}
