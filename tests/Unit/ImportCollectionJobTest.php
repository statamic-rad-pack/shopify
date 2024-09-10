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

class ImportCollectionJobTest extends TestCase
{
    /** @test */
    public function imports_collections()
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

        foreach ($collections as $collection) {
            Jobs\ImportCollectionJob::dispatch($collection);
        }

        $this->assertSame(3, Facades\Term::query()->where('taxonomy', 'collections')->count());

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

        foreach ($collections as $collection) {
            Jobs\ImportCollectionJob::dispatch($collection);
        }

        // check term data is added
        $term = Facades\Term::query()->where('taxonomy', 'collections')->first()->in('fr');

        // check translation data is added
        $this->assertSame($term->get('title'), 'Featured items');
    }
}
