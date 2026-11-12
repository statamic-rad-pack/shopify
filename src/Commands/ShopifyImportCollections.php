<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Traits\FetchCollections;

class ShopifyImportCollections extends Command
{
    use FetchCollections;
    use RunsInPlease;

    protected $signature = 'shopify:import:collections {--store= : The handle of a specific store to import from}';

    protected $description = 'Import the collections for all Shopify products stored in Statamic';

    public function handle()
    {
        $this->info('================================================================');
        $this->info('================= IMPORT SHOPIFY COLLECTIONS ===================');
        $this->info('================================================================');

        if (StoreConfig::isMultiStore()) {
            $storeOption = $this->option('store');

            $stores = $storeOption
                ? [$storeOption => config('shopify.multi_store.stores.'.$storeOption, [])]
                : config('shopify.multi_store.stores', []);

            foreach ($stores as $handle => $storeConfig) {
                $storeConfig['handle'] = $handle;
                $client = StoreConfig::makeGraphqlClient($storeConfig);

                $this->info('Importing collections for store: '.$handle);

                collect($this->getManualCollections($client))
                    ->merge($this->getSmartCollections($client))
                    ->each(function ($collectionId) use ($handle) {
                        ActivityLog::queueCollectionImport($collectionId, $handle, Activity::SOURCE_CLI);
                    });
            }
        } else {
            collect($this->getManualCollections())
                ->merge($this->getSmartCollections())
                ->each(function ($collectionId) {
                    ActivityLog::queueCollectionImport($collectionId, source: Activity::SOURCE_CLI);
                });
        }

        $this->info('Collections have been dispatched for import');
    }
}
