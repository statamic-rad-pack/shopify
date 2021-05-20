<?php

namespace Jackabox\Shopify\Commands;

use Illuminate\Console\Command;
use Jackabox\Shopify\Jobs\FetchCollectionsForProductJob;
use Jackabox\Shopify\Jobs\ImportAllProductsJob;
use Jackabox\Shopify\Traits\FetchAllProducts;
use PHPShopify\ShopifySDK;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Entry;

class ShopifyImportCollections extends Command
{
    use RunsInPlease;

    protected $signature = 'shopify:import:collections';
    protected $description = 'Import the collections for all Shopify products stored in Statamic';

    public function handle()
    {
        $this->info('================================================================');
        $this->info('================= IMPORT SHOPIFY COLLECTIONS ===================');
        $this->info('================================================================');

        $products = Entry::query()
            ->where('collection', 'products')
            ->get();

        foreach ($products as $product) {
            FetchCollectionsForProductJob::dispatch($product);
        }

        $this->info('Collections have been dispatched for import');
    }
}
