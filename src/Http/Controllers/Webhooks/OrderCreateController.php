<?php

namespace Jackabox\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Jackabox\Shopify\Jobs\ImportSingleProductJob;
use PHPShopify\ShopifySDK;

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
        $shopify = new ShopifySDK;

        foreach ($data->line_items as $item) {
            $product = $shopify->Product($item->product_id)->get();
            ImportSingleProductJob::dispatch($product);
        }

        return response()->json([], 200);
    }
}
