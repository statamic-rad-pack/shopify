<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Shopify\Jobs\FetchCollectionsForProductJob;

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
            'message' => 'Import has been queued.',
        ]);
    }
}
