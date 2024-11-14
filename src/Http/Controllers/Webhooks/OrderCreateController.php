<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Shopify\Clients\Rest;
use Statamic\Support\Arr;
use StatamicRadPack\Shopify\Events;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;

class OrderCreateController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        // Decode data
        $data = json_decode($request->getContent());

        // Fetch Single Product
        $client = app(Rest::class);

        foreach ($data->line_items as $item) {
            $response = $client->get(path: 'products/'.$item->product_id);

            if ($response->getStatusCode() == 200) {
                $product = Arr::get($response->getDecodedBody(), 'product', []);

                if ($product) {
                    ImportSingleProductJob::dispatch($product, [
                        'quantity' => [$item->sku => $item->quantity ?? 1],
                        'date' => Carbon::parse($data['created_at']),
                    ])->onQueue(config('shopify.queue'));
                }
            }
        }

        Events\OrderCreate::dispatch($data);

        return response()->json([], 200);
    }
}
