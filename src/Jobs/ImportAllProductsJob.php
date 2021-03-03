<?php

namespace Jackabox\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PHPShopify\ShopifySDK;

class ImportAllProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() { }

    public function handle()
    {
        $shopify = new ShopifySDK;
        $products = $shopify->Product->get();

        foreach ($products as $product) {
            ImportSingleProductJob::dispatch($product);
        }
    }
}
