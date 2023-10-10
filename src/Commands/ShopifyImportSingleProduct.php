<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Shopify\Clients\Rest;
use Statamic\Console\RunsInPlease;
use Statamic\Support\Arr;
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
        $client = app(Rest::class);
        $response = $client->get(path: 'products/'.$this->argument('productId'));

        if ($response->getStatusCode() != 200) {
            $this->error('Failed to retrieve product');
            return;
        }

        $product = Arr::get($response->getDecodedBody(), 'product', []);

        if (!$product) {
            $this->error('Failed to retrieve product');
            return;
        }

        // Pass to import Job.
        ImportSingleProductJob::dispatch($product)
            ->onQueue(config('shopify.queue'));

        $this->info('Product has been dispatched for import');
    }
}
