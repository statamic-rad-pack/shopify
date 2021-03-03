<?php

namespace Jackabox\Shopify\Commands;

use Illuminate\Console\Command;
use Jackabox\Shopify\Jobs\ImportAllProductsJob;
use Jackabox\Shopify\Jobs\ImportSingleProductJob;
use PHPShopify\ShopifySDK;
use Statamic\Console\RunsInPlease;

class ShopifyImportSingleProduct extends Command
{
    use RunsInPlease;

    protected $signature = 'shopify:import:product {productId}';
    protected $description = 'Imports a single products data from Shopify to Statamic';

    public function handle()
    {
        $this->info('================================================================');
        $this->info('=================== IMPORT SINGLE PRODUCT =====================');
        $this->info('================================================================');

        $this->info('Fetching data for product ' . $this->argument('productId'));

        // Fetch Single Product
        $shopify = new ShopifySDK;
        $product = $shopify->Product($this->argument('productId'))->get();

        // Pass to import Job.
        ImportSingleProductJob::dispatch($product);

        $this->info('Product has been dispatched for import');
    }
}
