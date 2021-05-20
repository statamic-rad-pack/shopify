<?php

namespace Jackabox\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use PHPShopify\ShopifySDK;
use Statamic\Http\Controllers\CP\CpController;

class ImportCollectionsController extends CpController
{
    public function fetchAll(): JsonResponse
    {
        $shopify = new ShopifySDK;

        $collectionResource = $shopify->Collection();

        ray($collectionResource);


        return response()->json([
            'message' => 'Import has been queued.'
        ]);
    }
}
