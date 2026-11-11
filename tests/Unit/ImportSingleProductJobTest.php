<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Entry;
use Statamic\Events\EntrySaved;
use Statamic\Facades;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Jobs;
use StatamicRadPack\Shopify\Tests\TestCase;

class ImportSingleProductJobTest extends TestCase
{
    #[Test]
    public function imports_product()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        Facades\Term::make()->taxonomy('collections')->slug('ipods')->merge([])->save();
        Facades\Term::make()->taxonomy('collections')->slug('ipods-1')->merge([])->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) {
                    return str_contains($query['query'], 'product(id');
                })
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: $this->getProductJson()
                ));
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $entry = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->first();

        $this->assertSame($entry->product_id, '108828309');
        $this->assertSame($entry->get('vendor'), ['arbor']);
        $this->assertSame($entry->get('type'), ['snowboards']);
        $this->assertSame($entry->get('collections'), ['ipods', 'ipods-1']);
    }

    #[Test]
    public function imports_translations_for_product()
    {
        Facades\Site::setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);

        Facades\Collection::make(config('shopify.collection_handle', 'products'))->sites(['en', 'fr'])->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) {
                    return str_contains($query['query'], 'product(id');
                })
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: $this->getProductJson()
                ));

            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) {
                    return str_contains($query['query'], 'translatableResourcesByIds(')
                        && $query['variables']['locale'] === 'fr'
                        && in_array('gid://shopify/Product/108828309', $query['variables']['resourceIds']);
                })
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                    "data": {
                      "translatableResourcesByIds": {
                        "nodes": [
                          {
                            "resourceId": "gid://shopify/Product/108828309",
                            "translations": [
                              {
                                "key": "title",
                                "value": "Featured items"
                              },
                              {
                                "key": "body_html",
                                "value": null
                              }
                            ]
                          }
                        ]
                      }
                    }
                    }'
                ));
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $entry = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->firstWhere('locale', 'fr');

        $this->assertNotNull($entry);
        $this->assertSame($entry->title, 'Featured items');
    }

    #[Test]
    public function updates_metafield_data()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->sites(['en', 'fr'])->dated(true)->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: $this->getProductJson()
                ));
        });

        Jobs\ImportSingleProductJob::dispatch(1072481042);

        $entry = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->first();

        $this->assertNotNull($entry);
        $this->assertSame($entry->get('some_metafield'), 'this is a value');
    }

    #[Test]
    public function updates_publish_status_based_on_shopify_resource_publications()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->sites(['en', 'fr'])->dated(true)->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $jsonResponse = str_replace('"resourcePublications": {}', '"resourcePublications": {
                            "edges": [
                              {
                                "node": {
                                  "isPublished": false,
                                  "publication": {
                                    "id": "gid://shopify/Publication/123756183775",
                                    "name": "Online Store"
                                  },
                                  "publishDate": "2064-05-13T14:06:54Z"
                                }
                              },
                              {
                                "node": {
                                  "isPublished": true,
                                  "publication": {
                                    "id": "gid://shopify/Publication/123756347615",
                                    "name": "Point of Sale"
                                  },
                                  "publishDate": "2024-05-13T14:06:54Z"
                                }
                              },
                              {
                                "node": {
                                  "isPublished": false,
                                  "publication": {
                                    "id": "gid://shopify/Publication/123756380383",
                                    "name": "Shop"
                                  },
                                  "publishDate": "2024-05-13T14:06:54Z"
                                }
                              }
                            ]
                          }', $this->getProductJson());

            $mock
                ->shouldReceive('query')
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: $jsonResponse
                ));
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $entry = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->first();

        $this->assertNotNull($entry);
        $this->assertSame($entry->published(), false);
        $this->assertSame($entry->date()->format('Y-m-d'), '2064-05-13');
    }

    #[Test]
    public function updates_changed_handle()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->sites(['en', 'fr'])->dated(true)->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $productJson = $this->getProductJson();
            $productJsonUpdated = str_replace('"handle": "product"', '"handle": "product-new"', $productJson);

            $mock
                ->shouldReceive('query')
                ->andReturn(
                    new HttpResponse(
                        status: 200,
                        body: $productJson
                    ),
                    new HttpResponse(
                        status: 200,
                        body: $productJsonUpdated
                    )
                );
        });

        Jobs\ImportSingleProductJob::dispatch(1072481042);

        $entry = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->first();

        $this->assertNotNull($entry);
        $this->assertSame($entry->slug(), 'product');

        Jobs\ImportSingleProductJob::dispatch(1072481042);

        $this->assertSame($entry->slug(), 'product-new');
    }

    #[Test]
    public function clears_product_metafields_removed_in_shopify()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $jsonWithMetafield = $this->getProductJson();
        $jsonWithoutMetafield = str_replace(
            '"edges": [
                                {
                                  "node": {
                                    "id" : 1,
                                    "key": "some_metafield",
                                    "value": "this is a value"
                                  }
                                }
                              ]',
            '"edges": []',
            $jsonWithMetafield
        );

        $this->mock(Graphql::class, function (MockInterface $mock) use ($jsonWithMetafield, $jsonWithoutMetafield) {
            $mock
                ->shouldReceive('query')
                ->andReturn(
                    new HttpResponse(status: 200, body: $jsonWithMetafield),
                    new HttpResponse(status: 200, body: $jsonWithoutMetafield)
                );
        });

        Jobs\ImportSingleProductJob::dispatch(1072481042);

        $entry = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->first();
        $this->assertSame($entry->get('some_metafield'), 'this is a value');

        Jobs\ImportSingleProductJob::dispatch(1072481042);

        $entry = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->first();
        $this->assertNull($entry->get('some_metafield'));
        $this->assertSame([], $entry->get('shopify_metafield_keys'));
    }

    #[Test]
    public function clears_variant_metafields_removed_in_shopify()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $jsonWithVariantMetafield = str_replace(
            '"metafields": {
                                        "edges": []
                                      }',
            '"metafields": {
                                        "edges": [
                                          {
                                            "node": {
                                              "id": 2,
                                              "key": "variant_metafield",
                                              "value": "variant value"
                                            }
                                          }
                                        ]
                                      }',
            $this->getProductJson()
        );

        $jsonWithoutVariantMetafield = str_replace(
            '"metafields": {
                                        "edges": [
                                          {
                                            "node": {
                                              "id": 2,
                                              "key": "variant_metafield",
                                              "value": "variant value"
                                            }
                                          }
                                        ]
                                      }',
            '"metafields": {
                                        "edges": []
                                      }',
            $jsonWithVariantMetafield
        );

        $this->mock(Graphql::class, function (MockInterface $mock) use ($jsonWithVariantMetafield, $jsonWithoutVariantMetafield) {
            $mock
                ->shouldReceive('query')
                ->andReturn(
                    new HttpResponse(status: 200, body: $jsonWithVariantMetafield),
                    new HttpResponse(status: 200, body: $jsonWithoutVariantMetafield)
                );
        });

        Jobs\ImportSingleProductJob::dispatch(1072481042);

        $variant = Facades\Entry::whereCollection('variants')->first();
        $this->assertSame($variant->get('variant_metafield'), 'variant value');

        Jobs\ImportSingleProductJob::dispatch(1072481042);

        $variant = Facades\Entry::whereCollection('variants')->first();
        $this->assertNull($variant->get('variant_metafield'));
        $this->assertSame([], $variant->get('shopify_metafield_keys'));
    }

    #[Test]
    public function skips_import_when_not_in_sales_channel()
    {
        config(['shopify.import_all_products' => false]);

        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Collection::make('variants')->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(
                status: 200,
                body: $this->getProductJson()
            ));
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $this->assertCount(0, Facades\Entry::whereCollection(config('shopify.collection_handle', 'products')));
        $this->assertCount(0, Facades\Entry::whereCollection('variants'));
    }

    #[Test]
    public function deletes_existing_product_when_removed_from_sales_channel()
    {
        config(['shopify.import_all_products' => false]);

        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Collection::make('variants')->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        // First import with the product in the sales channel
        $jsonWithChannel = str_replace('"resourcePublications": {}', '"resourcePublications": {
                            "edges": [
                              {
                                "node": {
                                  "isPublished": true,
                                  "publication": {
                                    "id": "gid://shopify/Publication/1",
                                    "name": "Online Store"
                                  },
                                  "publishDate": "2024-01-01T00:00:00Z"
                                }
                              }
                            ]
                          }', $this->getProductJson());

        $this->mock(Graphql::class, function (MockInterface $mock) use ($jsonWithChannel) {
            $mock->shouldReceive('query')->andReturn(
                new HttpResponse(status: 200, body: $jsonWithChannel),
                new HttpResponse(status: 200, body: $this->getProductJson())
            );
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $this->assertCount(1, Facades\Entry::whereCollection(config('shopify.collection_handle', 'products')));
        $this->assertCount(1, Facades\Entry::whereCollection('variants'));

        // Second import with the product no longer in the sales channel
        Jobs\ImportSingleProductJob::dispatch(1);

        $this->assertCount(0, Facades\Entry::whereCollection(config('shopify.collection_handle', 'products')));
        $this->assertCount(0, Facades\Entry::whereCollection('variants'));
    }

    #[Test]
    public function imports_product_when_in_sales_channel_and_filter_enabled()
    {
        config(['shopify.import_all_products' => false]);

        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $jsonWithChannel = str_replace('"resourcePublications": {}', '"resourcePublications": {
                            "edges": [
                              {
                                "node": {
                                  "isPublished": true,
                                  "publication": {
                                    "id": "gid://shopify/Publication/1",
                                    "name": "Online Store"
                                  },
                                  "publishDate": "2024-01-01T00:00:00Z"
                                }
                              }
                            ]
                          }', $this->getProductJson());

        $this->mock(Graphql::class, function (MockInterface $mock) use ($jsonWithChannel) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(
                status: 200,
                body: $jsonWithChannel
            ));
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $entry = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->first();
        $this->assertNotNull($entry);
        $this->assertSame($entry->product_id, '108828309');
    }

    #[Test]
    public function uses_shopify_inventory_policy_when_variant_is_tracked()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Collection::make('variants')->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: $this->getProductJson()));
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $variant = Facades\Entry::whereCollection('variants')->first();
        $this->assertSame('DENY', $variant->get('inventory_policy'));
    }

    #[Test]
    public function sets_inventory_policy_to_continue_when_variant_is_not_tracked()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Collection::make('variants')->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $json = str_replace('"tracked": true', '"tracked": false', $this->getProductJson());

        $this->mock(Graphql::class, function (MockInterface $mock) use ($json) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: $json));
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $variant = Facades\Entry::whereCollection('variants')->first();
        $this->assertSame('CONTINUE', $variant->get('inventory_policy'));
    }

    #[Test]
    public function import_product_query_hook_can_modify_the_query()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $seenProductId = null;
        $seenStoreHandle = 'not-set';

        Jobs\ImportSingleProductJob::hook('import-product-query', function ($payload, $next) use (&$seenProductId, &$seenStoreHandle) {
            $seenProductId = $payload->productId;
            $seenStoreHandle = $payload->storeHandle;

            $payload->query = str_replace('descriptionHtml', 'descriptionHtml # hook-applied', $payload->query);

            return $next($payload);
        });

        $receivedQuery = null;

        $this->mock(Graphql::class, function (MockInterface $mock) use (&$receivedQuery) {
            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) use (&$receivedQuery) {
                    if (str_contains($query['query'], 'product(id')) {
                        $receivedQuery = $query['query'];
                    }

                    return true;
                })
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: $this->getProductJson()
                ));
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $this->assertSame(1, $seenProductId);
        $this->assertNull($seenStoreHandle);
        $this->assertStringContainsString('# hook-applied', $receivedQuery);
    }

    #[Test]
    public function import_product_data_hook_can_modify_data_before_it_is_merged()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $seenEntry = null;
        $seenProduct = null;

        Jobs\ImportSingleProductJob::hook('import-product-data', function ($payload, $next) use (&$seenEntry, &$seenProduct) {
            $seenEntry = $payload->entry;
            $seenProduct = $payload->product;

            $data = $payload->data;
            $data['title'] = 'Hooked title';
            $payload->data = $data;

            return $next($payload);
        });

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: $this->getProductJson()
                ));
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $entry = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->first();

        $this->assertSame('Hooked title', $entry->title);
        $this->assertInstanceOf(Entry::class, $seenEntry);
        $this->assertSame('108828309', $seenProduct['id']);
    }

    private function getProductJson(): string
    {
        return '{
                     "data": {
                          "product": {
                            "collections": {
                              "edges": [
                                {
                                  "node": {
                                    "handle": "ipods"
                                  }
                                },
                                {
                                  "node": {
                                    "handle": "ipods-1"
                                  }
                                }
                              ]
                            },
                            "createdAt": "2005-01-02T00:00:00Z",
                            "defaultCursor": "eyJsaW1pdCI6MSwib3JkZXIiOiJpZCBhc2MiLCJsYXN0X2lkIjoxMDg4MjgzMDksImxhc3RfdmFsdWUiOjEwODgyODMwOSwiZGlyZWN0aW9uIjoibmV4dCJ9",
                            "description": "good board",
                            "descriptionHtml": "<p>good board</p>",
                            "featuredImage": {
                              "id": "gid://shopify/ProductImage/183532652"
                            },
                            "feedback": null,
                            "giftCardTemplateSuffix": null,
                            "handle": "product",
                            "hasOnlyDefaultVariant": false,
                            "hasOutOfStockVariants": false,
                            "id": "gid://shopify/Product/108828309",
                            "images": {
                              "edges": []
                            },
                            "inCollection": true,
                            "isGiftCard": false,
                            "legacyResourceId": "108828309",
                            "metafield": null,
                            "metafields": {
                              "edges": [
                                {
                                  "node": {
                                    "id" : 1,
                                    "key": "some_metafield",
                                    "value": "this is a value"
                                  }
                                }
                              ]
                            },
                            "onlineStorePreviewUrl": "https://www.snowdevil.ca/products/draft",
                            "onlineStoreUrl": "https://www.snowdevil.ca/products/draft",
                            "options": [
                              {
                                "name": "Title"
                              }
                            ],
                            "priceRange": {
                              "maxVariantPrice": {
                                "amount": "1000.0"
                              },
                              "minVariantPrice": {
                                "amount": "1000.0"
                              }
                            },
                            "productType": "Snowboards",
                            "resourcePublicationsCount": {
                              "count": 4
                            },
                            "availablePublicationsCount": {
                              "count": 4
                            },
                            "publishedAt": "2005-01-02T00:00:00Z",
                            "resourcePublications": {},
                            "resourcePublicationOnCurrentPublication": {
                              "publication": {
                                "name": "Generic Channel",
                                "id": "gid://shopify/Publication/762454635"
                              },
                              "publishDate": "2005-01-02T00:00:00Z",
                              "isPublished": true
                            },
                            "seo": {
                              "title": null
                            },
                            "storefrontId": "gid://shopify/Product/108828309",
                            "tags": [
                              "Deepsnow",
                              "Dub Quote\"s",
                              "quote\'s",
                              "Wooden Core"
                            ],
                            "templateSuffix": null,
                            "title": "Draft",
                            "totalInventory": 1,
                            "tracksInventory": true,
                            "unpublishedPublications": {
                              "edges": [
                                {
                                  "node": {
                                    "name": ""
                                  }
                                },
                                {
                                  "node": {
                                    "name": ""
                                  }
                                },
                                {
                                  "node": {
                                    "name": ""
                                  }
                                },
                                {
                                  "node": {
                                    "name": ""
                                  }
                                },
                                {
                                  "node": {
                                    "name": "Private app with all permissions"
                                  }
                                }
                              ]
                            },
                            "updatedAt": "2005-01-02T00:00:00Z",
                            "variants": {
                              "edges": [
                                {
                                  "node": {
                                      "compareAtPrice": null,
                                      "id": "gid://shopify/ProductVariant/1",
                                      "inventoryItem": {
                                        "measurement": {
                                          "weight": {
                                            "value": 0
                                          }
                                        },
                                        "requiresShipping": true,
                                        "tracked": true
                                      },
                                      "inventoryPolicy": "DENY",
                                      "inventoryQuantity": 0,
                                      "media": {
                                        "edges": []
                                      },
                                      "metafields": {
                                        "edges": []
                                      },
                                      "price": "23.00",
                                      "selectedOptions": [
                                        {
                                          "name": "Colour",
                                          "optionValue": {
                                            "id": "gid://shopify/ProductOptionValue/3"
                                          },
                                          "value": "Oxford Navy"
                                        },
                                        {
                                          "name": "Size",
                                          "optionValue": {
                                            "id": "gid://shopify/ProductOptionValue/2"
                                          },
                                          "value": "X Small (3-4yrs)"
                                        }
                                      ],
                                      "sku": "",
                                      "title": "Oxford Navy / X Small (3-4yrs)"
                                  }
                                }
                              ]
                            },
                            "variantsCount": {
                              "count": 1
                            },
                            "vendor": "Arbor"
                          }
                       }
                    }';
    }

    #[Test]
    public function accumulates_purchase_history_across_orders()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Collection::make('variants')->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: $this->getProductJson()));
        });

        // variant 1 in the fixture has an empty SKU, so it must be matched by variant id
        Jobs\ImportSingleProductJob::dispatch(1, ['quantity' => ['1' => 2], 'date' => Carbon::parse('2024-03-05')]);
        Jobs\ImportSingleProductJob::dispatch(1, ['quantity' => ['1' => 3], 'date' => Carbon::parse('2024-04-01')]);

        $variant = Facades\Entry::whereCollection('variants')->first();
        $this->assertSame(5, $variant->get('total_purchased')['lifetime']);
        $this->assertSame(5, $variant->get('total_purchased')[2024]['total']);
        $this->assertSame(2, $variant->get('total_purchased')[2024]['03']);
        $this->assertSame(3, $variant->get('total_purchased')[2024]['04']);
        $this->assertSame('2024-04-01 00:00:00', $variant->get('last_purchased'));

        $product = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->first();
        $this->assertSame(5, $product->get('total_purchased')['lifetime']);
    }

    #[Test]
    public function stores_a_price_and_stock_summary_on_the_product()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Collection::make('variants')->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: $this->getProductJson()));
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $product = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->first();

        $this->assertSame('23.00', $product->get('price_min'));
        $this->assertSame('23.00', $product->get('price_max'));
        $this->assertFalse($product->get('in_stock'));
        $this->assertFalse($product->get('on_sale'));
    }

    #[Test]
    public function saves_the_product_entry_once_per_import()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Collection::make('variants')->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: $this->getProductJson()));
        });

        $saves = 0;
        Event::listen(EntrySaved::class, function ($event) use (&$saves) {
            if ($event->entry->collectionHandle() === config('shopify.collection_handle', 'products')) {
                $saves++;
            }
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $this->assertSame(1, $saves);
    }

    #[Test]
    public function fetches_product_and_variant_translations_in_one_query_per_site()
    {
        Facades\Site::setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);

        Facades\Collection::make(config('shopify.collection_handle', 'products'))->sites(['en', 'fr'])->save();
        Facades\Collection::make('variants')->sites(['en', 'fr'])->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $translationQueries = [];

        $product = json_decode($this->getProductJson(), true);
        $product['data']['product']['options'] = [
            ['id' => 'gid://shopify/ProductOption/1', 'name' => 'Colour', 'optionValues' => [['id' => 'gid://shopify/ProductOptionValue/3', 'name' => 'Oxford Navy']]],
            ['id' => 'gid://shopify/ProductOption/2', 'name' => 'Size', 'optionValues' => [['id' => 'gid://shopify/ProductOptionValue/2', 'name' => 'X Small (3-4yrs)']]],
        ];

        $this->mock(Graphql::class, function (MockInterface $mock) use (&$translationQueries, $product) {
            $mock->shouldReceive('query')->andReturnUsing(function ($query) use (&$translationQueries, $product) {
                if (! str_contains($query['query'], 'translatableResourcesByIds(')) {
                    return new HttpResponse(status: 200, body: json_encode($product));
                }

                $translationQueries[] = $query['variables'];

                return new HttpResponse(status: 200, body: json_encode(['data' => ['translatableResourcesByIds' => ['nodes' => [
                    ['resourceId' => 'gid://shopify/Product/108828309', 'translations' => [['key' => 'title', 'value' => 'Produit']]],
                    ['resourceId' => 'gid://shopify/ProductOption/1', 'translations' => [['key' => 'name', 'value' => 'Couleur']]],
                    ['resourceId' => 'gid://shopify/ProductOptionValue/3', 'translations' => [['key' => 'name', 'value' => 'Bleu marine']]],
                ]]]]));
            });
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        // product, its options and option values; variants aren't translatable resources
        $this->assertCount(1, $translationQueries);
        $this->assertContains('gid://shopify/Product/108828309', $translationQueries[0]['resourceIds']);
        $this->assertContains('gid://shopify/ProductOption/1', $translationQueries[0]['resourceIds']);
        $this->assertContains('gid://shopify/ProductOptionValue/3', $translationQueries[0]['resourceIds']);
        $this->assertEmpty(preg_grep('/ProductVariant/', $translationQueries[0]['resourceIds']));

        $product = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->firstWhere('locale', 'fr');
        $this->assertSame('Produit', $product->get('title'));
        $this->assertSame('Couleur', $product->get('options')['option1']);

        $variant = Facades\Entry::whereCollection('variants')->firstWhere('locale', 'fr');
        $this->assertSame('Bleu marine', $variant->get('option1'));
        $this->assertSame('Bleu marine / X Small (3-4yrs)', $variant->get('title'));
    }

    #[Test]
    public function does_not_import_the_same_product_concurrently()
    {
        $middleware = (new Jobs\ImportSingleProductJob(123, [], 'uk'))->middleware();

        $this->assertInstanceOf(WithoutOverlapping::class, $middleware[0]);
        $this->assertSame('shopify-product-123-uk', $middleware[0]->key);
    }

    #[Test]
    public function imports_variants_beyond_the_first_page()
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Collection::make('variants')->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $firstPage = json_decode($this->getProductJson(), true);
        $firstPage['data']['product']['variants']['pageInfo'] = ['hasNextPage' => true, 'endCursor' => 'cursor-1'];

        $secondVariant = $firstPage['data']['product']['variants']['edges'][0];
        $secondVariant['node']['id'] = 'gid://shopify/ProductVariant/2';
        $secondVariant['node']['title'] = 'Second';

        $cursors = [];

        $this->mock(Graphql::class, function (MockInterface $mock) use ($firstPage, $secondVariant, &$cursors) {
            $mock->shouldReceive('query')->andReturnUsing(function ($query) use ($firstPage, $secondVariant, &$cursors) {
                if (str_contains($query['query'], 'after: $cursor')) {
                    $cursors[] = $query['variables']['cursor'];

                    return new HttpResponse(status: 200, body: json_encode(['data' => ['product' => ['variants' => [
                        'edges' => [$secondVariant],
                        'pageInfo' => ['hasNextPage' => false, 'endCursor' => null],
                    ]]]]));
                }

                return new HttpResponse(status: 200, body: json_encode($firstPage));
            });
        });

        Jobs\ImportSingleProductJob::dispatch(1);

        $this->assertSame(['cursor-1'], $cursors);
        $this->assertCount(2, Facades\Entry::whereCollection('variants'));
        $this->assertNotNull(Facades\Entry::whereCollection('variants')->firstWhere('title', 'Second'));
    }
}
