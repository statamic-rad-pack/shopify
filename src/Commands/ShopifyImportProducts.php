<?php

namespace Jackabox\Shopify\Commands;

use Illuminate\Console\Command;
use Jackabox\Shopify\Jobs\ImportAllProductsJob;
use Jackabox\Shopify\Traits\FetchAllProducts;
use PHPShopify\ShopifySDK;
use Statamic\Console\RunsInPlease;

class ShopifyImportProducts extends Command
{
    use RunsInPlease, FetchAllProducts;

    protected $signature = 'shopify:import:all';
    protected $description = 'Imports your products from Shopify to Statamic';

    public function handle()
    {
        $this->info('================================================================');
        $this->info('=================== IMPORT SHOPIFY PRODUCTS ====================');
        $this->info('================================================================');

        $this->fetchProducts();

        $this->info('Products have been dispatched for import');
    }
}
