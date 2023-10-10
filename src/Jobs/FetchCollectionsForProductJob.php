<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shopify\Clients\Rest;
use Statamic\Facades\Taxonomy;
use Statamic\Support\Arr;

class FetchCollectionsForProductJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $product;

    public $shopify;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function handle()
    {
        // Check if we have published the collection taxonomy.
        if (! Taxonomy::findByHandle(config('shopify.taxonomies.collections'))) {
            return;
        }

        $this->shopify = app(Rest::class);

        $collections = [];

        $collections = array_merge($collections, $this->getManualCollections());
        $collections = array_merge($collections, $this->getSmartCollections());

        ImportCollectionsForProductJob::dispatch($collections, $this->product)
            ->onQueue(config('shopify.queue'));
    }

    public function getManualCollections()
    {
        return $this->loopCollections('custom_collections');
    }

    public function getSmartCollections()
    {
        return $this->loopCollections('smart_collections');
    }

    private function loopCollections($resource)
    {
        $items = [];

        $response = $this->shopify->get(path: $resource, query: ['limit' => config('shopify.api_limit'), 'product_id' => $this->product->get('product_id')]);
        $nextPage = $response->getPageInfo();

        if ($response->getStatusCode() == 200) {

            $collections = Arr::get($response->getDecodedBody(), $resource, []);
            $items = array_merge($items, $collections);

            while ($nextPage) {
                $response = $this->shopify->get(path: $resource, query: $nextPage);
                $collections = Arr::get($response->getDecodedBody(), $resource, []);

                $nextPage = $response->getPageInfo();

                $items = array_merge($items, $collections);
            }

        }

        return $items;
    }
}
