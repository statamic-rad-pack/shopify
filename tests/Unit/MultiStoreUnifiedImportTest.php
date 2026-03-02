<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Statamic\Facades;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Tests\TestCase;

class MultiStoreUnifiedImportTest extends TestCase
{
    private function multiStoreConfig(): array
    {
        return [
            'enabled' => true,
            'mode' => 'unified',
            'primary_store' => 'uk',
            'stores' => [
                'uk' => [
                    'url' => 'uk-store.myshopify.com',
                    'admin_token' => 'uk-token',
                    'api_version' => '2025-04',
                ],
                'us' => [
                    'url' => 'us-store.myshopify.com',
                    'admin_token' => 'us-token',
                    'api_version' => '2025-04',
                ],
            ],
        ];
    }

    private function setupCollectionsAndTaxonomies(): void
    {
        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Taxonomy::make()->handle('collections')->save();
        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('type')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();
    }

    private function mockGraphqlForStore(string $storeHandle): void
    {
        $mock = $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(
                new HttpResponse(status: 200, body: $this->getProductJson())
            );
        });

        // Also bind the store-specific key so StoreConfig::makeGraphqlClient returns the mock
        $this->app->instance('shopify.graphql.'.$storeHandle, $mock);
    }

    #[Test]
    public function writes_multi_store_data_on_variant_for_non_primary_store()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());
        $this->setupCollectionsAndTaxonomies();
        $this->mockGraphqlForStore('us');

        ImportSingleProductJob::dispatch(108828309, [], 'us');

        $variant = Facades\Entry::whereCollection('variants')->first();

        $this->assertNotNull($variant);

        $multiStoreData = $variant->get('multi_store_data', []);

        $this->assertArrayHasKey('us', $multiStoreData);
        $this->assertSame('23.00', $multiStoreData['us']['price']);
    }

    #[Test]
    public function primary_store_updates_top_level_pricing_fields()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());
        $this->setupCollectionsAndTaxonomies();
        $this->mockGraphqlForStore('uk');

        ImportSingleProductJob::dispatch(108828309, [], 'uk');

        $variant = Facades\Entry::whereCollection('variants')->first();

        $this->assertNotNull($variant);

        // Top-level price should be set for primary store
        $this->assertNotNull($variant->get('price'));
        $this->assertSame('23.00', $variant->get('price'));

        // multi_store_data should also be set
        $multiStoreData = $variant->get('multi_store_data', []);
        $this->assertArrayHasKey('uk', $multiStoreData);
    }

    #[Test]
    public function non_primary_store_does_not_overwrite_top_level_pricing_fields()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());
        $this->setupCollectionsAndTaxonomies();

        // First import from primary store to set top-level fields
        $this->mockGraphqlForStore('uk');
        ImportSingleProductJob::dispatch(108828309, [], 'uk');

        $variant = Facades\Entry::whereCollection('variants')->first();
        $ukPrice = $variant->get('price');

        // Now import from non-primary store with a different price via different JSON
        $usMock = $this->mock(Graphql::class, function (MockInterface $mock) {
            $usJson = str_replace('"price": "23.00"', '"price": "29.99"', $this->getProductJson());
            $mock->shouldReceive('query')->andReturn(
                new HttpResponse(status: 200, body: $usJson)
            );
        });
        $this->app->instance('shopify.graphql.us', $usMock);

        ImportSingleProductJob::dispatch(108828309, [], 'us');

        $variant->fresh();
        $variant = Facades\Entry::whereCollection('variants')->first();

        // Top-level price should NOT have been overwritten by non-primary store
        $this->assertSame($ukPrice, $variant->get('price'));

        // US store data should be in multi_store_data
        $multiStoreData = $variant->get('multi_store_data', []);
        $this->assertArrayHasKey('us', $multiStoreData);
        $this->assertSame('29.99', $multiStoreData['us']['price']);
    }

    #[Test]
    public function skips_translation_loop_for_multi_store_job()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());
        $this->setupCollectionsAndTaxonomies();

        Facades\Site::setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);

        Facades\Collection::make(config('shopify.collection_handle', 'products'))->sites(['en', 'fr'])->save();

        $callCount = 0;
        $mock = $this->mock(Graphql::class, function (MockInterface $mock) use (&$callCount) {
            $mock->shouldReceive('query')
                ->andReturnUsing(function () use (&$callCount) {
                    $callCount++;

                    return new HttpResponse(status: 200, body: $this->getProductJson());
                });
        });
        $this->app->instance('shopify.graphql.uk', $mock);

        ImportSingleProductJob::dispatch(108828309, [], 'uk');

        // Only 1 query should have been made (the initial product fetch),
        // not 3 (product + 2 translation queries for the fr site variants and product)
        $this->assertSame(1, $callCount);
    }

    private function getProductJson(): string
    {
        return '{
            "data": {
                "product": {
                    "collections": {"edges": []},
                    "descriptionHtml": "<p>Test product</p>",
                    "handle": "test-product",
                    "id": "gid://shopify/Product/108828309",
                    "metafields": {"edges": []},
                    "media": {"edges": []},
                    "options": [{"name": "Title", "values": ["Default Title"]}],
                    "productType": "Test",
                    "resourcePublications": {},
                    "tags": [],
                    "title": "Test Product",
                    "variants": {
                        "edges": [
                            {
                                "node": {
                                    "compareAtPrice": null,
                                    "id": "gid://shopify/ProductVariant/1",
                                    "inventoryItem": {
                                        "measurement": {"weight": {"value": 0, "unit": "KILOGRAMS"}},
                                        "requiresShipping": true
                                    },
                                    "inventoryPolicy": "DENY",
                                    "inventoryQuantity": 10,
                                    "media": {"edges": []},
                                    "metafields": {"edges": []},
                                    "price": "23.00",
                                    "selectedOptions": [{"name": "Title", "optionValue": {"id": "gid://shopify/ProductOptionValue/1"}, "value": "Default Title"}],
                                    "sku": "TEST-SKU",
                                    "title": "Default Title"
                                }
                            }
                        ]
                    },
                    "vendor": "Test Vendor"
                }
            }
        }';
    }
}
