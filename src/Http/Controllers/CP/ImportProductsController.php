<?php

namespace Jackabox\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jackabox\Shopify\Jobs\ImportAllProductsJob;
use Jackabox\Shopify\Jobs\ImportSingleProductJob;
use PHPShopify\ShopifySDK;
use Statamic\Http\Controllers\CP\CpController;

class ImportProductsController extends CpController
{
    public function fetchAll(): JsonResponse
    {
        ImportAllProductsJob::dispatch();

        return response()->json([
            'message' => 'Import has been queued.'
        ]);
    }

    public function fetchSingleProduct(Request $request): JsonResponse
    {
        // Fetch Single Product
        $shopify = new ShopifySDK;
        $product = $shopify->Product($request->get('product'))->get();

        // Pass to import Job.
        ImportSingleProductJob::dispatch($product);

        return response()->json([
            'message' => 'Product import has been queued.'
        ]);
    }
}
