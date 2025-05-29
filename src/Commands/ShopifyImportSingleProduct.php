<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
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

        // Pass to import Job.
        ImportSingleProductJob::dispatch($this->argument('productId'));

        $this->info('Product has been dispatched for import');
    }
}
