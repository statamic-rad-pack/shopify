<?php

namespace StatamicRadPack\Shopify\Traits;

use Shopify\Clients\Rest;
use Statamic\Support\Arr;
use StatamicRadPack\Shopify\Jobs\ImportAllProductsJob;

trait FetchAllProducts
{
    public function fetchProducts()
    {
        $client = app(Rest::class);
        $response = $client->get(path: 'products', query: ['limit' => config('shopify.api_limit')]);
        $nextPage = $response->getPageInfo();

        if ($response->getStatusCode() == 200) {

            // Initial Loop
            $this->callJob($response);

            // Recursively loop.
            while ($nextPage) {
                $response = $client->get(path: 'products', query: $nextPage);
                $nextPage = $response->getPageInfo();

                $this->callJob($response);
            }

        }
    }

    private function callJob($response)
    {
        ImportAllProductsJob::dispatch(Arr::get($response->getDecodedBody(), 'products', []))
            ->onQueue(config('shopify.queue'));
    }
}
