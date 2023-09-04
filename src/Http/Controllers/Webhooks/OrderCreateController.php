<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use PHPShopify\ShopifySDK;
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
        $shopify = new ShopifySDK();

        foreach ($data->line_items as $item) {
            $product = $shopify->Product($item->product_id)->get();
            ImportSingleProductJob::dispatch($product)->onQueue(config('shopify.queue'));
        }

        return response()->json([], 200);
    }
}
