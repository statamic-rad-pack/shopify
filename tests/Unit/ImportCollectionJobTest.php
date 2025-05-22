<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Statamic\Facades;
use StatamicRadPack\Shopify\Jobs;
use StatamicRadPack\Shopify\Tests\TestCase;

class ImportCollectionJobTest extends TestCase
{
    #[Test]
    public function imports_a_collection()
    {
        Facades\Taxonomy::make()->handle('collections')->save();

        Facades\Entry::make()
            ->collection('products')
            ->id('product-1')
            ->slug('product-1')
            ->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "collection": {
                          "descriptionHtml": "<p>Test description.</p>",
                          "handle": "test-title",
                          "id": "gid://shopify/Collection/841564295",
                          "image": null,
                          "metafields": {
                            "edges": [
                              {
                                "node": {
                                  "id": "gid://shopify/Metafield/1069228981",
                                  "jsonValue": true,
                                  "key": "sponsor",
                                  "value": "Shopify"
                                }
                              }
                            ]
                          },
                          "title": "Test title"
                        }
                      }
                    }'
                ));
        });

        $this->assertSame(0, Facades\Term::query()->where('taxonomy', 'collections')->count());

        Jobs\ImportCollectionJob::dispatch(841564295);

        $this->assertSame(1, Facades\Term::query()->where('taxonomy', 'collections')->count());

        // check term data is added
        $term = Facades\Term::query()->where('taxonomy', 'collections')->first();
        $this->assertSame($term->get('collection_id'), 841564295);

        // check metadata is added
        $this->assertSame($term->get('sponsor'), 'Shopify');
    }

    #[Test]
    public function imports_collections_translations_for_product()
    {
        Facades\Site::setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);

        Facades\Taxonomy::make()->handle('collections')->sites(['en', 'fr'])->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) {
                    return str_contains($query['query'], 'collection(id:');
                })
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "collection": {
                          "descriptionHtml": "<p>Test description.</p>",
                          "handle": "test-title",
                          "id": "gid://shopify/Collection/841564295",
                          "image": null,
                          "metafields": {
                            "edges": [
                              {
                                "node": {
                                  "id": "gid://shopify/Metafield/1069228981",
                                  "jsonValue": true,
                                  "key": "sponsor",
                                  "value": "Shopify"
                                }
                              }
                            ]
                          },
                          "title": "Test title"
                        }
                      }
                    }'
                ));

            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) {
                    return str_contains($query['query'], 'translatableResource(resourceId:');
                })
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "translatableResource": {
                        "resourceId": "gid://shopify/Collection/1007901140",
                        "translatableContent": [
                          {
                            "key": "title",
                            "value": "Featured items",
                            "digest": "a18b34037fda5b1afd720d4b85b86a8a75b5e389452f84f5b6d2b8e210869fd7",
                            "locale": "en"
                          },
                          {
                            "key": "body_html",
                            "value": null,
                            "digest": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
                            "locale": "en"
                          },
                          {
                            "key": "meta_title",
                            "value": null,
                            "digest": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
                            "locale": "en"
                          },
                          {
                            "key": "meta_description",
                            "value": null,
                            "digest": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
                            "locale": "en"
                          }
                        ]
                      }
                    }'
                ));
        });

        Jobs\ImportCollectionJob::dispatch(841564295);

        // check term data is added
        $term = Facades\Term::query()->where('taxonomy', 'collections')->first()->in('fr');

        // check translation data is added
        $this->assertSame($term->get('title'), 'Featured items');
    }
}
