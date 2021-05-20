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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;

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

        $shopify = new ShopifySDK;

        $collectionResource = $shopify->CustomCollection();
        $collections = $collectionResource->get([
            'limit' => config('shopify.api_limit'),
            'product_id' => $this->product->data()['product_id']
        ]);

        $next_page = $collectionResource->getNextPageParams();

        // Initial Loop
        ImportCollectionsForProductJob::dispatch($collections, $this->product);

        // Recursively loop.
        while ($next_page) {
            $collections = $collectionResource->get($collectionResource->getNextPageParams());
            $next_page = $collectionResource->getNextPageParams();

            ImportCollectionsForProductJob::dispatch($collections, $this->product);
        }
    }
}
