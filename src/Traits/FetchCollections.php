<?php

namespace StatamicRadPack\Shopify\Traits;

use Shopify\Clients\Rest;
use Statamic\Support\Arr;

trait FetchCollections
{
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

        $response = app(Rest::class)->get(path: $resource, query: ['limit' => config('shopify.api_limit')]);

        $nextPage = ($response->getPageInfo()?->getNextPageUrl() ?? false) ? $response->getPageInfo() : false;

        if ($response->getStatusCode() == 200) {

            $collections = Arr::get($response->getDecodedBody(), $resource, []);
            $items = array_merge($items, $collections);

            while ($nextPage) {
                $response = app(Rest::class)->get(path: $resource, query: $nextPage->getNextPageQuery());
                $collections = Arr::get($response->getDecodedBody(), $resource, []);

                $nextPage = ($response->getPageInfo()?->getNextPageUrl() ?? false) ? $response->getPageInfo() : false;

                $items = array_merge($items, $collections);
            }

        }

        return $items;
    }
}
