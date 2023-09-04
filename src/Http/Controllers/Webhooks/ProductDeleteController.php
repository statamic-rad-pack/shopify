<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
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

        if (! is_object($data) && ! $data->id) {
            return;
        }

        $productEntry = Entry::query()
            ->where('collection', 'products')
            ->where('product_id', $data->id)
            ->first();

        if ($productEntry && $productEntry->slug()) {
            $entry = Entry::query()
                ->where('collection', 'variants')
                ->where('product_slug', $productEntry->slug())
                ->get();

            foreach ($entry as $e) {
                $e->delete();
            }

            $productEntry->delete();
        }

        return response()->json([
            'message' => 'Product has been deleted',
        ], 200);
    }
}
