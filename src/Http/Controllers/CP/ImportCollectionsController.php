<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;
use StatamicRadPack\Shopify\Jobs;

class ImportCollectionsController extends CpController
{
    public function fetchAll(): JsonResponse
    {
        collect([])
            ->merge($this->getManualCollections())
            ->merge($this->getSmartCollections())
            ->each(function ($collection) {
                Jobs\ImportCollectionJob::dispatch($collection)->onQueue(config('shopify.queue'));
            });

        $products = Entry::query()
            ->where('collection', 'products')
            ->get();

        foreach ($products as $product) {
            Jobs\FetchCollectionsForProductJob::dispatch($product)->onQueue(config('shopify.queue'));
        }

        return response()->json([
            'message' => 'Import has been queued.',
        ]);
    }

    public function getManualCollections()
    {
        return $this->loopCollections('custom_collections');
    }

    public function getSmartCollections()
    {
        return $this->loopCollections('smart_collections');
    }

    private function loopCollections($resource)
    {
        $items = [];

        $response = $this->shopify->get(path: $resource, query: ['limit' => config('shopify.api_limit')]);
        $nextPage = $response->getPageInfo();

        if ($response->getStatusCode() == 200) {

            $collections = Arr::get($response->getDecodedBody(), $resource, []);
            $items = array_merge($items, $collections);

            while ($nextPage) {
                $response = $this->shopify->get(path: $resource, query: $nextPage->getNextPageQuery());
                $collections = Arr::get($response->getDecodedBody(), $resource, []);

                $nextPage = $response->getPageInfo();

                $items = array_merge($items, $collections);
            }

        }

        return $items;
    }
}
