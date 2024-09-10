<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use StatamicRadPack\Shopify\Jobs\ImportCollectionJob;
use StatamicRadPack\Shopify\Traits\FetchCollections;

class ShopifyImportCollections extends Command
{
    use FetchCollections;
    use RunsInPlease;

    protected $signature = 'shopify:import:collections';

    protected $description = 'Import the collections for all Shopify products stored in Statamic';

    public function handle()
    {
        $this->info('================================================================');
        $this->info('================= IMPORT SHOPIFY COLLECTIONS ===================');
        $this->info('================================================================');

        collect([])
            ->merge($this->getManualCollections())
            ->merge($this->getSmartCollections())
            ->each(function ($collection) {
                ImportCollectionJob::dispatch($collection)->onQueue(config('shopify.queue'));
            });

        $this->info('Collections have been dispatched for import');
    }
}
