<?php

namespace Jackabox\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Jackabox\Shopify\Jobs\ImportSingleProductJob;
use PHPShopify\ShopifySDK;
use Statamic\Facades\Entry;

class ProductDeleteController extends WebhooksController
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

        $productEntry =  Entry::query()
            ->where('collection', 'products')
            ->where('product_id', $data->id)
            ->get();

        if ($productEntry->count()) {
            Entry::query()
                ->where('collection', 'variants')
                ->where('product_slug', $productEntry->slug())
                ->delete();

            $productEntry->delete();
        }


        return response()->json([
            'message' => 'Product has been dispatched to the queue for update'
        ], 200);
    }
}
