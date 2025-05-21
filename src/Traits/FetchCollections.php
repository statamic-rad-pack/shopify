<?php

namespace StatamicRadPack\Shopify\Traits;

use Shopify\Clients\Graphql;
use Statamic\Support\Arr;
use Statamic\Support\Str;

trait FetchCollections
{
    private int $loopCollectionsPaginationCount = 100;

    public function getManualCollections()
    {
        return $this->loopCollections('custom');
    }

    public function getSmartCollections()
    {
        return $this->loopCollections('smart');
    }

    private function loopCollections($resource)
    {
        $items = [];

        $query = <<<QUERY
            query (\$numItems: Int!, \$cursor: String) {
              collections(first: \$numItems, after: \$cursor, query: "collection_type:$resource") {
                nodes {
                  id
                }
                pageInfo {
                  hasNextPage
                  endCursor
                }
              }
            }
            QUERY;

        $data = [];

        do {
            $response = app(Graphql::class)->query([
                'query' => $query,
                'variables' => [
                    'numItems' => $this->loopCollectionsPaginationCount,
                    'cursor' => Arr::get($data, 'data.collections.pageInfo.endCursor', null),
                ],
            ]);

            $data = $response->getDecodedBody();

            if ($collections = Arr::get($data, 'data.collections.nodes', [])) {
                $items = array_merge($items, collect($collections)->map(fn ($collection) => (int) Str::afterLast($collection['id'], '/'))->all());
            }

        } while (Arr::get($data, 'data.collections.pageInfo.hasNextPage', false));

        return $items;
    }
}
