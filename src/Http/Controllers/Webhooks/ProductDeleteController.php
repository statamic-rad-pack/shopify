<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use StatamicRadPack\Shopify\Events;

class ProductDeleteController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        // Decode data
        $data = json_decode($request->getContent());

        if (! is_object($data) || ! $data->id) {
            return;
        }

        Events\ProductDelete::dispatch($data);

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
