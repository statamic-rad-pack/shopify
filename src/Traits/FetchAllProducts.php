<?php

namespace Jackabox\Shopify\Traits;

use Jackabox\Shopify\Jobs\ImportAllProductsJob;
use PHPShopify\ShopifySDK;

trait FetchAllProducts
{
    public function fetchProducts()
    {
        $shopify = new ShopifySDK;

        $productResource = $shopify->Product();
        $products = $productResource->get(['limit' => config('shopify.api_limit')]);
        $next_page = $productResource->getNextPageParams();

        // Initial Loop
        ImportAllProductsJob::dispatch($products);

        // Recursively loop.
        while ($next_page) {
            $products = $productResource->get($productResource->getNextPageParams());
            $next_page = $productResource->getNextPageParams();

            ImportAllProductsJob::dispatch($products);
        }
    }
}
