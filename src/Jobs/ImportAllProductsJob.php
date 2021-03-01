<?php

namespace Jackabox\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PHPShopify\Exception\ApiException;
use PHPShopify\Exception\CurlException;
use PHPShopify\ShopifySDK;

class ImportAllProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {

    }

    public function handle()
    {
        $config = [
            'ShopUrl' => config('shopify.auth.url'),
            'ApiKey' => config('shopify.auth.key'),
            'Password' => config('shopify.auth.password'),
        ];

        ShopifySDK::config($config);

        $shopify = new ShopifySDK;

        try {
            $products = $shopify->Product->get();

            foreach ($products as $product) {
                ImportSingleProductJob::dispatch($product);
            }
        } catch (ApiException $e) {

        } catch (CurlException $e) {

        }
    }
}
