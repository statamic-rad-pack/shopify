<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;

class ProductsController extends CpController
{
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }

        $products = Entry::query()
            ->where('collection', config('shopify.collection_handle', 'products'))
            ->get()
            ->map(function ($product) {
                $values['title'] = $product->title;
                $values['product_id'] = $product->product_id;

                return $values;
            });

        return response()->json([
            'products' => $products,
            'message' => 'Import has been queued.',
        ]);
    }
}
