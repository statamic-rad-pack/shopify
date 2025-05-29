<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Tests\TestCase;
use StatamicRadPack\Shopify\Traits\FetchAllProducts;

class FetchProductsTest extends TestCase
{
    use FetchAllProducts;

    #[Test]
    public function gets_product_ids_from_graphql()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->once()
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "products": {
                          "nodes": [
                            {
                              "id": "gid://shopify/Product/1"
                            },
                            {
                              "id": "gid://shopify/Product/2"
                            },
                            {
                              "id": "gid://shopify/Product/3"
                            },
                            {
                              "id": "gid://shopify/Product/4"
                            },
                            {
                              "id": "gid://shopify/Product/5"
                            },
                            {
                              "id": "gid://shopify/Product/6"
                            },
                            {
                              "id": "gid://shopify/Product/7"
                            },
                            {
                              "id": "gid://shopify/Product/8"
                            },
                            {
                              "id": "gid://shopify/Product/9"
                            },
                            {
                              "id": "gid://shopify/Product/10"
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

        $products = $this->fetchProducts();

        $this->assertCount(10, $products);
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $products);
    }

    #[Test]
    public function handles_paginated_responses()
    {
        $this->loopProductsPaginationCount = 5;

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
                        "products": {
                          "nodes": [
                            {
                              "id": "gid://shopify/Product/1"
                            },
                            {
                              "id": "gid://shopify/Product/2"
                            },
                            {
                              "id": "gid://shopify/Product/3"
                            },
                            {
                              "id": "gid://shopify/Product/4"
                            },
                            {
                              "id": "gid://shopify/Product/5"
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
                        "products": {
                          "nodes": [
                            {
                              "id": "gid://shopify/Product/6"
                            },
                            {
                              "id": "gid://shopify/Product/7"
                            },
                            {
                              "id": "gid://shopify/Product/8"
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

        $products = $this->fetchProducts();

        $this->assertCount(8, $products);
    }
}
