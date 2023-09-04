<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use PHPShopify\ShopifySDK;
use Statamic\Console\RunsInPlease;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;

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

        $this->info('Fetching data for product '.$this->argument('productId'));

        // Fetch Single Product
        $shopify = new ShopifySDK();
        $product = $shopify->Product($this->argument('productId'))->get();

        // Pass to import Job.
        ImportSingleProductJob::dispatch($product)
            ->onQueue(config('shopify.queue'));

        $this->info('Product has been dispatched for import');
    }
}
