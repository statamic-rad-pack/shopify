<?php

namespace StatamicRadPack\Shopify\Traits;

use PHPShopify\ShopifySDK;
use StatamicRadPack\Shopify\Jobs\ImportAllProductsJob;

trait FetchAllProducts
{
    public function fetchProducts()
    {
        $shopify = new ShopifySDK();

        $productResource = $shopify->Product();
        $products = $productResource->get(['limit' => config('shopify.api_limit')]);
        $next_page = $productResource->getNextPageParams();

        // Initial Loop
        ImportAllProductsJob::dispatch($products)
            ->onQueue(config('shopify.queue'));

        // Recursively loop.
        while ($next_page) {
            $products = $productResource->get($productResource->getNextPageParams());
            $next_page = $productResource->getNextPageParams();

            ImportAllProductsJob::dispatch($products)
                ->onQueue(config('shopify.queue'));
        }
    }
}
