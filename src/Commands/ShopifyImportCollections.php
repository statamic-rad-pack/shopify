<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Entry;
use StatamicRadPack\Shopify\Jobs\FetchCollectionsForProductJob;

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
            FetchCollectionsForProductJob::dispatch($product)
                ->onQueue(config('shopify.queue'));
        }

        $this->info('Collections have been dispatched for import');
    }
}
