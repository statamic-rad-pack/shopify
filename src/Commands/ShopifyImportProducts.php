<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Traits\FetchAllProducts;

class ShopifyImportProducts extends Command
{
    use FetchAllProducts, RunsInPlease;

    protected $signature = 'shopify:import:all {--store= : The handle of a specific store to import from}';

    protected $description = 'Imports your products from Shopify to Statamic';

    public function handle()
    {
        $this->info('================================================================');
        $this->info('=================== IMPORT SHOPIFY PRODUCTS ====================');
        $this->info('================================================================');

        if (StoreConfig::isMultiStore()) {
            $storeOption = $this->option('store');

            $stores = $storeOption
                ? [$storeOption => config('shopify.multi_store.stores.'.$storeOption, [])]
                : config('shopify.multi_store.stores', []);

            foreach ($stores as $handle => $storeConfig) {
                $storeConfig['handle'] = $handle;
                $client = StoreConfig::makeGraphqlClient($storeConfig);

                $this->info('Importing products for store: '.$handle);

                collect($this->fetchProducts($client))
                    ->each(function ($productId) use ($handle) {
                        $this->callJob($productId, $handle);
                    });
            }
        } else {
            collect($this->fetchProducts())
                ->each(function ($productId) {
                    $this->callJob($productId);
                });
        }

        $this->info('Products have been dispatched for import');
    }
}
