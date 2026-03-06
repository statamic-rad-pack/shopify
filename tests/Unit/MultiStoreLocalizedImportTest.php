<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Statamic\Facades;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Tests\TestCase;

class MultiStoreLocalizedImportTest extends TestCase
{
    private function localizedConfig(): array
    {
        return [
            'enabled' => true,
            'mode' => 'localized',
            'primary_store' => 'uk',
            'stores' => [
                'uk' => [
                    'url' => 'uk-store.myshopify.com',
                    'admin_token' => 'uk-token',
                    'api_version' => '2025-04',
                    'site' => 'en',
                ],
                'us' => [
                    'url' => 'us-store.myshopify.com',
                    'admin_token' => 'us-token',
                    'api_version' => '2025-04',
                    'site' => 'fr',
                ],
            ],
        ];
    }

    private function setupMultisite(): void
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
    }

    private function mockGraphqlForStore(string $storeHandle, ?string $productJson = null): void
    {
        $json = $productJson ?? $this->getProductJson();
        $mock = $this->mock(Graphql::class, function (MockInterface $mock) use ($json) {
            $mock->shouldReceive('query')->andReturn(
                new HttpResponse(status: 200, body: $json)
            );
        });

        $this->app->instance('shopify.graphql.'.$storeHandle, $mock);
    }

    #[Test]
    public function imports_product_into_correct_site_for_localized_mode()
    {
        config()->set('shopify.multi_store', $this->localizedConfig());
        $this->setupMultisite();
        $this->mockGraphqlForStore('us');

        ImportSingleProductJob::dispatch(108828309, [], 'us');

        // Product entry should be in the 'fr' site (mapped from 'us' store)
        $entry = Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))
            ->firstWhere('locale', 'fr');

        $this->assertNotNull($entry);
        $this->assertSame('108828309', $entry->product_id);
    }

    #[Test]
    public function imports_variant_into_correct_site_for_localized_mode()
    {
        config()->set('shopify.multi_store', $this->localizedConfig());
        $this->setupMultisite();
        $this->mockGraphqlForStore('us');

        ImportSingleProductJob::dispatch(108828309, [], 'us');

        // Variant should be in the 'fr' site
        $variant = Facades\Entry::whereCollection('variants')->firstWhere('locale', 'fr');

        $this->assertNotNull($variant);
    }

    #[Test]
    public function skips_translation_loop_in_localized_mode()
    {
        config()->set('shopify.multi_store', $this->localizedConfig());
        $this->setupMultisite();

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

        // Only 1 query: the initial product fetch — no translation queries
        $this->assertSame(1, $callCount);
    }

    #[Test]
    public function does_not_set_multi_store_data_field_in_localized_mode()
    {
        config()->set('shopify.multi_store', $this->localizedConfig());
        $this->setupMultisite();
        $this->mockGraphqlForStore('uk');

        ImportSingleProductJob::dispatch(108828309, [], 'uk');

        $variant = Facades\Entry::whereCollection('variants')->first();

        $this->assertNotNull($variant);
        $this->assertNull($variant->get('multi_store_data'));
    }

    private function getProductJson(): string
    {
        return '{
            "data": {
                "product": {
                    "collections": {"edges": []},
                    "descriptionHtml": "<p>Localized product</p>",
                    "handle": "localized-product",
                    "id": "gid://shopify/Product/108828309",
                    "metafields": {"edges": []},
                    "media": {"edges": []},
                    "options": [{"name": "Title", "values": ["Default Title"]}],
                    "productType": "Test",
                    "resourcePublications": {},
                    "tags": [],
                    "title": "Localized Product",
                    "variants": {
                        "edges": [
                            {
                                "node": {
                                    "compareAtPrice": null,
                                    "id": "gid://shopify/ProductVariant/99",
                                    "inventoryItem": {
                                        "measurement": {"weight": {"value": 0, "unit": "KILOGRAMS"}},
                                        "requiresShipping": true
                                    },
                                    "inventoryPolicy": "DENY",
                                    "inventoryQuantity": 5,
                                    "media": {"edges": []},
                                    "metafields": {"edges": []},
                                    "price": "49.99",
                                    "selectedOptions": [{"name": "Title", "optionValue": {"id": "gid://shopify/ProductOptionValue/1"}, "value": "Default Title"}],
                                    "sku": "LOCALIZED-SKU",
                                    "title": "Default Title"
                                }
                            }
                        ]
                    },
                    "vendor": "Localized Vendor"
                }
            }
        }';
    }
}
