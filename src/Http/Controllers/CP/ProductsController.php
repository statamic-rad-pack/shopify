<?php

namespace Jackabox\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jackabox\Shopify\Jobs\ImportAllProductsJob;
use Jackabox\Shopify\Jobs\ImportSingleProductJob;
use PHPShopify\ShopifySDK;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;

class ProductsController extends CpController
{
    public function index(): JsonResponse
    {
        $products = Entry::query()
            ->where('collection', 'products')
            ->get()
            ->map(function ($product) {
                $values['title'] = $product->title;
                $values['product_id'] = $product->product_id;
                return $values;
            });

        return response()->json([
            'products' => $products,
            'message' => 'Import has been queued.'
        ]);
    }
}
