<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Tests\TestCase;
use StatamicRadPack\Shopify\Traits\FetchCollections;

class FetchCollectionsTest extends TestCase
{
    use FetchCollections;

    #[Test]
    public function gets_collection_ids_from_graphql()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) {
                    return str_contains($query['query'], 'query: "collection_type:smart');
                })
                ->once()
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "collections": {
                          "nodes": [
                            {
                              "id": "gid://shopify/Collection/1"
                            },
                            {
                              "id": "gid://shopify/Collection/2"
                            },
                            {
                              "id": "gid://shopify/Collection/3"
                            },
                            {
                              "id": "gid://shopify/Collection/4"
                            },
                            {
                              "id": "gid://shopify/Collection/5"
                            },
                            {
                              "id": "gid://shopify/Collection/6"
                            },
                            {
                              "id": "gid://shopify/Collection/7"
                            },
                            {
                              "id": "gid://shopify/Collection/8"
                            },
                            {
                              "id": "gid://shopify/Collection/9"
                            },
                            {
                              "id": "gid://shopify/Collection/10"
                            }
                          ],
                          "pageInfo": {
                            "hasNextPage": false,
                            "endCursor": null
                          }
                        }
                      }
                    }'
                ));
        });

        $collections = $this->getSmartCollections();

        $this->assertCount(10, $collections);
    }

    #[Test]
    public function handles_paginated_responses()
    {
        $this->loopCollectionsPaginationCount = 5;

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) {
                    return $query['variables']['cursor'] === null;
                })
                ->once()
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "collections": {
                          "nodes": [
                            {
                              "id": "gid://shopify/Collection/1"
                            },
                            {
                              "id": "gid://shopify/Collection/2"
                            },
                            {
                              "id": "gid://shopify/Collection/3"
                            },
                            {
                              "id": "gid://shopify/Collection/4"
                            },
                            {
                              "id": "gid://shopify/Collection/5"
                            }
                          ],
                          "pageInfo": {
                            "hasNextPage": true,
                            "endCursor": "test-cursor"
                          }
                        }
                      }
                    }'
                ));

            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) {
                    return $query['variables']['cursor'] !== null;
                })
                ->once()
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "collections": {
                          "nodes": [
                            {
                              "id": "gid://shopify/Collection/6"
                            },
                            {
                              "id": "gid://shopify/Collection/7"
                            },
                            {
                              "id": "gid://shopify/Collection/8"
                            }
                          ],
                          "pageInfo": {
                            "hasNextPage": false,
                            "endCursor": null
                          }
                        }
                      }
                    }'
                ));
        });

        $collections = $this->getSmartCollections();

        $this->assertCount(8, $collections);
    }
}
