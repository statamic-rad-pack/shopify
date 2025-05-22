<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use StatamicRadPack\Shopify\Traits\FetchAllProducts;

class ShopifyImportProducts extends Command
{
    use FetchAllProducts, RunsInPlease;

    protected $signature = 'shopify:import:all';

    protected $description = 'Imports your products from Shopify to Statamic';

    public function handle()
    {
        $this->info('================================================================');
        $this->info('=================== IMPORT SHOPIFY PRODUCTS ====================');
        $this->info('================================================================');

        collect($this->fetchProducts())
            ->each(function ($productId) {
                $this->callJob($productId);
            });

        $this->info('Products have been dispatched for import');
    }
}
