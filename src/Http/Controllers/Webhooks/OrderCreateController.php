<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Shopify\Clients\Rest;
use Statamic\Support\Arr;
use StatamicRadPack\Shopify\Events;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;

class OrderCreateController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        $hmac_header = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $verified = $this->verify($data, $hmac_header);

        if (! $verified) {
            return response()->json(['error' => true], 403);
        }

        // Decode data
        $data = json_decode($data);

        // Fetch Single Product
        $shopify = app(Rest::class);

        foreach ($data->line_items as $item) {
            $product = $shopify->Product($item->product_id)->get();

            $response = $client->get(path: 'products/'.$item->product_id);

            if ($response->getStatusCode() == 200) {
                $product = Arr::get($response->getDecodedBody(), 'product', []);

                if ($product) {
                    ImportSingleProductJob::dispatch($product)->onQueue(config('shopify.queue'));
                }
            }
        }

        Events\OrderCreate::dispatch($data);

        return response()->json([], 200);
    }
}
