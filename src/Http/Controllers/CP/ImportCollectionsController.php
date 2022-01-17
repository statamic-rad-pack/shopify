<?php

namespace Jackabox\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Jackabox\Shopify\Jobs\FetchCollectionsForProductJob;
use Jackabox\Shopify\Jobs\ImportCollectionsForProductJob;
use PHPShopify\ShopifySDK;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Http\Controllers\CP\CpController;

class ImportCollectionsController extends CpController
{
    public function fetchAll(): JsonResponse
    {
        $products = Entry::query()
            ->where('collection', 'products')
            ->get();

        foreach ($products as $product) {
            FetchCollectionsForProductJob::dispatch($product)->onQueue(config('shopify.queue'));
        }

        return response()->json([
            'message' => 'Import has been queued.'
        ]);
    }
}
