<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Shopify\Clients\Rest;
use Shopify\Clients\RestResponse;
use Statamic\Facades;
use StatamicRadPack\Shopify\Jobs;
use StatamicRadPack\Shopify\Tests\TestCase;

class JobsTest extends TestCase
{
    /** @test */
    public function imports_collections_for_product()
    {
        Facades\Taxonomy::make()->handle('collections')->save();

        $product = tap(Facades\Entry::make()
            ->collection('products')
            ->id('product-1')
            ->slug('product-1')
        )->save();

        $this->mock(Rest::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{
                      "metafields": [
                        {
                          "id": 1069228981,
                          "namespace": "my_fields",
                          "key": "sponsor",
                          "value": "Shopify",
                          "description": null,
                          "owner_id": 382285388,
                          "created_at": "2023-10-03T13:26:51-04:00",
                          "updated_at": "2023-10-03T13:26:51-04:00",
                          "owner_resource": "blog",
                          "type": "single_line_text_field",
                          "admin_graphql_api_id": "gid://shopify/Metafield/1069228981"
                        }
                      ]
                    }'
                ));
        });

        $collections = json_decode('[{"id": 841564295,"handle": "ipods","title": "IPods","updated_at": "2008-02-01T19:00:00-05:00","body_html": "<p>The best selling ipod ever</p>","published_at": "2008-02-01T19:00:00-05:00","sort_order": "manual","template_suffix": null,"published_scope": "web","admin_graphql_api_id": "gid://shopify/Collection/841564295"},{"id": 395646240,"handle": "ipods_two","title": "IPods Two","updated_at": "2008-02-01T19:00:00-05:00","body_html": "<p>The best selling ipod ever. Again</p>","published_at": "2008-02-01T19:00:00-05:00","sort_order": "manual","template_suffix": null,"published_scope": "web","admin_graphql_api_id": "gid://shopify/Collection/395646240"},{"id": 691652237,"handle": "non-ipods","title": "Non Ipods","updated_at": "2013-02-01T19:00:00-05:00","body_html": "<p>No ipods here</p>","published_at": "2013-02-01T19:00:00-05:00","sort_order": "manual","template_suffix": null,"published_scope": "web","admin_graphql_api_id": "gid://shopify/Collection/691652237"}]', true);

        Jobs\ImportCollectionsForProductJob::dispatch($collections, $product);

        // check collection terms are created
        $this->assertSame(['ipods', 'ipods_two', 'non-ipods'], $product->fresh()->collections);

        // check term data is added
        $term = Facades\Term::query()->where('taxonomy', 'collections')->first();
        $this->assertSame($term->get('collection_id'), 841564295);

        // check metadata is added
        $this->assertSame($term->get('sponsor'), 'Shopify');
    }

    /** @test */
    public function imports_collections_translations_for_product()
    {
        Facades\Site::setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);

        Facades\Taxonomy::make()->handle('collections')->sites(['en', 'fr'])->save();

        $product = tap(Facades\Entry::make()
            ->collection('products')
            ->id('product-1')
            ->slug('product-1')
        )->save();

        $this->mock(Rest::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{
                      "metafields": [
                        {
                          "id": 1069228981,
                          "namespace": "my_fields",
                          "key": "sponsor",
                          "value": "Shopify",
                          "description": null,
                          "owner_id": 382285388,
                          "created_at": "2023-10-03T13:26:51-04:00",
                          "updated_at": "2023-10-03T13:26:51-04:00",
                          "owner_resource": "blog",
                          "type": "single_line_text_field",
                          "admin_graphql_api_id": "gid://shopify/Metafield/1069228981"
                        }
                      ]
                    }'
                ));
        });

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
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

        $collections = json_decode('[{"id": 841564295,"handle": "ipods","title": "IPods","updated_at": "2008-02-01T19:00:00-05:00","body_html": "<p>The best selling ipod ever</p>","published_at": "2008-02-01T19:00:00-05:00","sort_order": "manual","template_suffix": null,"published_scope": "web","admin_graphql_api_id": "gid://shopify/Collection/841564295"},{"id": 395646240,"handle": "ipods_two","title": "IPods Two","updated_at": "2008-02-01T19:00:00-05:00","body_html": "<p>The best selling ipod ever. Again</p>","published_at": "2008-02-01T19:00:00-05:00","sort_order": "manual","template_suffix": null,"published_scope": "web","admin_graphql_api_id": "gid://shopify/Collection/395646240"},{"id": 691652237,"handle": "non-ipods","title": "Non Ipods","updated_at": "2013-02-01T19:00:00-05:00","body_html": "<p>No ipods here</p>","published_at": "2013-02-01T19:00:00-05:00","sort_order": "manual","template_suffix": null,"published_scope": "web","admin_graphql_api_id": "gid://shopify/Collection/691652237"}]', true);

        Jobs\ImportCollectionsForProductJob::dispatch($collections, $product);

        // check term data is added
        $term = Facades\Term::query()->where('taxonomy', 'collections')->first()->in('fr');

        // check translation data is added
        $this->assertSame($term->get('title'), 'Featured items');
    }

    /** @test */
    public function imports_product()
    {
        $data = json_decode('{
            "id": 1072481042,
            "title": "Burton Custom Freestyle 151",
            "body_html": "<strong>Good snowboard!</strong>",
            "vendor": "Burton",
            "product_type": "Snowboard",
            "created_at": "2023-10-03T13:23:57-04:00",
            "handle": "burton-custom-freestyle-151",
            "updated_at": "2023-10-03T13:23:57-04:00",
            "published_at": null,
            "template_suffix": null,
            "published_scope": "web",
            "tags": "",
            "status": "draft",
            "admin_graphql_api_id": "gid://shopify/Product/1072481042",
            "variants": [
              {
                "id": 1070325019,
                "product_id": 1072481042,
                "title": "Default Title",
                "price": "0.00",
                "sku": "",
                "position": 1,
                "inventory_policy": "deny",
                "compare_at_price": null,
                "fulfillment_service": "manual",
                "inventory_management": null,
                "option1": "Default Title",
                "option2": null,
                "option3": null,
                "created_at": "2023-10-03T13:23:57-04:00",
                "updated_at": "2023-10-03T13:23:57-04:00",
                "taxable": true,
                "barcode": null,
                "grams": 0,
                "image_id": null,
                "weight": 0,
                "weight_unit": "lb",
                "inventory_item_id": 1070325019,
                "inventory_quantity": 0,
                "old_inventory_quantity": 0,
                "presentment_prices": [
                  {
                    "price": {
                      "amount": "0.00",
                      "currency_code": "USD"
                    },
                    "compare_at_price": null
                  }
                ],
                "requires_shipping": true,
                "admin_graphql_api_id": "gid://shopify/ProductVariant/1070325019"
              }
            ],
            "options": [
              {
                "id": 1055547176,
                "product_id": 1072481042,
                "name": "Title",
                "position": 1,
                "values": [
                  "Default Title"
                ]
              }
            ],
            "images": [],
            "image": null
        }', true);

        Facades\Collection::make('products')->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $this->mock(Rest::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->with('custom_collections', [], ['limit' => 30, 'product_id' => 1072481042])
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{
                      "custom_collections": [
                        {
                          "id": 841564295,
                          "handle": "ipods",
                          "title": "IPods",
                          "updated_at": "2008-02-01T19:00:00-05:00",
                          "body_html": "<p>The best selling ipod ever</p>",
                          "published_at": "2008-02-01T19:00:00-05:00",
                          "sort_order": "manual",
                          "template_suffix": null,
                          "published_scope": "web",
                          "admin_graphql_api_id": "gid://shopify/Collection/841564295"
                        }
                    ]
                }'
                ));

            $mock
                ->shouldReceive('get')
                ->with('smart_collections', [], ['limit' => 30, 'product_id' => 1072481042])
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{
                      "smart_collections": [
                        {
                          "id": 1063001323,
                          "handle": "ipods-1",
                          "title": "IPods",
                          "updated_at": "2023-10-03T13:23:35-04:00",
                          "body_html": null,
                          "published_at": "2023-10-03T13:23:35-04:00",
                          "sort_order": "best-selling",
                          "template_suffix": null,
                          "disjunctive": false,
                          "rules": [
                            {
                              "column": "title",
                              "relation": "starts_with",
                              "condition": "iPod"
                            }
                          ],
                          "published_scope": "web",
                          "admin_graphql_api_id": "gid://shopify/Collection/1063001323"
                        }
                      ]
                }'
                ));
        });

        Jobs\ImportSingleProductJob::dispatch($data);

        $entry = Facades\Entry::whereCollection('products')->first();

        $this->assertSame($entry->product_id, 1072481042);
        $this->assertSame($entry->get('vendor'), ['burton']);
        $this->assertSame($entry->get('type'), ['snowboard']);
        $this->assertSame($entry->get('collections'), ['ipods', 'ipods-1']);
    }

    /** @test */
    public function imports_translations_for_product()
    {
        Facades\Site::setConfig(['sites' => [
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]]);

        $data = json_decode('{
            "id": 1072481042,
            "title": "Burton Custom Freestyle 151",
            "body_html": "<strong>Good snowboard!</strong>",
            "vendor": "Burton",
            "product_type": "Snowboard",
            "created_at": "2023-10-03T13:23:57-04:00",
            "handle": "burton-custom-freestyle-151",
            "updated_at": "2023-10-03T13:23:57-04:00",
            "published_at": null,
            "template_suffix": null,
            "published_scope": "web",
            "tags": "",
            "status": "draft",
            "admin_graphql_api_id": "gid://shopify/Product/1072481042",
            "variants": [
              {
                "id": 1070325019,
                "product_id": 1072481042,
                "title": "Default Title",
                "price": "0.00",
                "sku": "",
                "position": 1,
                "inventory_policy": "deny",
                "compare_at_price": null,
                "fulfillment_service": "manual",
                "inventory_management": null,
                "option1": "Default Title",
                "option2": null,
                "option3": null,
                "created_at": "2023-10-03T13:23:57-04:00",
                "updated_at": "2023-10-03T13:23:57-04:00",
                "taxable": true,
                "barcode": null,
                "grams": 0,
                "image_id": null,
                "weight": 0,
                "weight_unit": "lb",
                "inventory_item_id": 1070325019,
                "inventory_quantity": 0,
                "old_inventory_quantity": 0,
                "presentment_prices": [
                  {
                    "price": {
                      "amount": "0.00",
                      "currency_code": "USD"
                    },
                    "compare_at_price": null
                  }
                ],
                "requires_shipping": true,
                "admin_graphql_api_id": "gid://shopify/ProductVariant/1070325019"
              }
            ],
            "options": [
              {
                "id": 1055547176,
                "product_id": 1072481042,
                "name": "Title",
                "position": 1,
                "values": [
                  "Default Title"
                ]
              }
            ],
            "images": [],
            "image": null
        }', true);

        Facades\Collection::make('products')->sites(['en', 'fr'])->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();

        $this->mock(Rest::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->with('custom_collections', [], ['limit' => 30, 'product_id' => 1072481042])
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{
                      "custom_collections": [
                        {
                          "id": 841564295,
                          "handle": "ipods",
                          "title": "IPods",
                          "updated_at": "2008-02-01T19:00:00-05:00",
                          "body_html": "<p>The best selling ipod ever</p>",
                          "published_at": "2008-02-01T19:00:00-05:00",
                          "sort_order": "manual",
                          "template_suffix": null,
                          "published_scope": "web",
                          "admin_graphql_api_id": "gid://shopify/Collection/841564295"
                        }
                    ]
                }'
                ));

            $mock
                ->shouldReceive('get')
                ->with('smart_collections', [], ['limit' => 30, 'product_id' => 1072481042])
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{
                      "smart_collections": [
                        {
                          "id": 1063001323,
                          "handle": "ipods-1",
                          "title": "IPods",
                          "updated_at": "2023-10-03T13:23:35-04:00",
                          "body_html": null,
                          "published_at": "2023-10-03T13:23:35-04:00",
                          "sort_order": "best-selling",
                          "template_suffix": null,
                          "disjunctive": false,
                          "rules": [
                            {
                              "column": "title",
                              "relation": "starts_with",
                              "condition": "iPod"
                            }
                          ],
                          "published_scope": "web",
                          "admin_graphql_api_id": "gid://shopify/Collection/1063001323"
                        }
                      ]
                }'
                ));
        });

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
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

        Jobs\ImportSingleProductJob::dispatch($data);

        $entry = Facades\Entry::whereCollection('products')->firstWhere('locale', 'fr');

        $this->assertNotNull($entry);
        $this->assertSame($entry->title, 'Featured items');
    }
}
