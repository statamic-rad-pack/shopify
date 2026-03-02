<?php

namespace StatamicRadPack\Shopify\Traits;

use Shopify\Clients\Graphql;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;

trait FetchAllProducts
{
    private int $loopProductsPaginationCount = 100;

    public function fetchProducts(?Graphql $client = null)
    {
        $client = $client ?? app(Graphql::class);

        $items = [];

        $query = <<<'QUERY'
            query ($numItems: Int!, $cursor: String) {
              products(first: $numItems, after: $cursor) {
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
            $response = $client->query([
                'query' => $query,
                'variables' => [
                    'numItems' => $this->loopProductsPaginationCount,
                    'cursor' => Arr::get($data, 'data.products.pageInfo.endCursor', null),
                ],
            ]);

            $data = $response->getDecodedBody();

            if ($products = Arr::get($data, 'data.products.nodes', [])) {
                $items = array_merge($items, collect($products)->map(fn ($product) => (int) Str::afterLast($product['id'], '/'))->all());
            }

        } while (Arr::get($data, 'data.products.pageInfo.hasNextPage', false));

        return $items;
    }

    private function callJob(int $productId, ?string $storeHandle = null)
    {
        ImportSingleProductJob::dispatch($productId, [], $storeHandle);
    }
}
