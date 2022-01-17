<?php

namespace Jackabox\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PHPShopify\ShopifySDK;
use Statamic\Facades\Taxonomy;

class FetchCollectionsForProductJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $product;
    public $shopify;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function handle()
    {
        // Check if we have published the collection taxonomy.
        if (! Taxonomy::findByHandle(config('shopify.taxonomies.collections'))) {
            return;
        }

        $this->shopify = new ShopifySDK();

        $collections = [];

        $collections = array_merge($collections, $this->getManualCollections());
        $collections = array_merge($collections, $this->getSmartCollections());

        ImportCollectionsForProductJob::dispatch($collections, $this->product)
            ->onQueue(config('shopify.queue'));
    }

    public function getManualCollections()
    {
        $collectionResource = $this->shopify->CustomCollection();

        return $this->loopCollections($collectionResource);
    }

    public function getSmartCollections()
    {
        $smartCollectionResource = $this->shopify->SmartCollection();

        return $this->loopCollections($smartCollectionResource);
    }

    private function loopCollections($resource)
    {
        $items = [];

        $collections = $resource->get([
            'limit' => config('shopify.api_limit'),
            'product_id' => $this->product->data()['product_id']
        ]);

        $next_page = $resource->getNextPageParams();

        $items = array_merge($items, $collections);

        while ($next_page) {
            $collections = $resource->get($resource->getNextPageParams());
            $next_page = $resource->getNextPageParams();

            $items = array_merge($items, $collections);
        }

        return $items;
    }
}
