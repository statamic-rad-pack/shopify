<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Statamic\Facades;
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
                    return str_contains($query['query'], 'translatableResource(resourceId: "');
                })
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                    "data": {
                      "translatableResource": {
                        "resourceId": "gid://shopify/Product/1007901140",
                        "translations": [
                          {
                            "key": "title",
                            "value": "Featured items"
                          },
                          {
                            "key": "body_html",
                            "value": null
                          },
                          {
                            "key": "meta_title",
                            "value": null
                          },
                          {
                            "key": "meta_description",
                            "value": null
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
                                        "requiresShipping": true
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
}
