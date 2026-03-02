<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Statamic\Facades;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Tests\TestCase;

class MultiStoreMarketsImportTest extends TestCase
{
    private function marketsConfig(): array
    {
        return [
            'enabled' => true,
            'mode' => 'markets',
            'markets' => [
                'GB' => ['currency' => '£'],
                'IE' => ['currency' => '€'],
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

    private function mockGraphql(): void
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(
                new HttpResponse(status: 200, body: $this->getProductJson())
            );
        });
    }

    #[Test]
    public function writes_market_data_to_variant_entry()
    {
        config()->set('shopify.multi_store', $this->marketsConfig());
        $this->setupCollectionsAndTaxonomies();
        $this->mockGraphql();

        ImportSingleProductJob::dispatch(108828309);

        $variant = Facades\Entry::whereCollection('variants')->first();

        $this->assertNotNull($variant);

        $marketData = $variant->get('market_data', []);

        $this->assertArrayHasKey('GB', $marketData);
        $this->assertArrayHasKey('IE', $marketData);
    }

    #[Test]
    public function market_data_contains_correct_price_from_contextual_pricing()
    {
        config()->set('shopify.multi_store', $this->marketsConfig());
        $this->setupCollectionsAndTaxonomies();
        $this->mockGraphql();

        ImportSingleProductJob::dispatch(108828309);

        $variant = Facades\Entry::whereCollection('variants')->first();

        $this->assertNotNull($variant);

        $marketData = $variant->get('market_data', []);

        $this->assertSame('9.99', $marketData['GB']['price']);
        $this->assertSame('12.99', $marketData['IE']['price']);
    }

    #[Test]
    public function market_data_contains_summed_inventory_by_country_code()
    {
        config()->set('shopify.multi_store', $this->marketsConfig());
        $this->setupCollectionsAndTaxonomies();
        $this->mockGraphql();

        ImportSingleProductJob::dispatch(108828309);

        $variant = Facades\Entry::whereCollection('variants')->first();

        $this->assertNotNull($variant);

        $marketData = $variant->get('market_data', []);

        // GB has two locations: 6 + 4 = 10
        $this->assertSame(10, $marketData['GB']['inventory_quantity']);
        // IE has one location: 5
        $this->assertSame(5, $marketData['IE']['inventory_quantity']);
    }

    #[Test]
    public function top_level_price_fields_are_not_affected_by_markets_mode()
    {
        config()->set('shopify.multi_store', $this->marketsConfig());
        $this->setupCollectionsAndTaxonomies();
        $this->mockGraphql();

        ImportSingleProductJob::dispatch(108828309);

        $variant = Facades\Entry::whereCollection('variants')->first();

        $this->assertNotNull($variant);

        // Top-level price comes from the standard variant price field, not contextual pricing
        $this->assertSame('23.00', $variant->get('price'));
        $this->assertSame(15, $variant->get('inventory_quantity'));
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
                                        "requiresShipping": true,
                                        "inventoryLevels": {
                                            "nodes": [
                                                {
                                                    "location": {"address": {"countryCode": "GB"}},
                                                    "quantities": [{"name": "available", "quantity": 6}]
                                                },
                                                {
                                                    "location": {"address": {"countryCode": "GB"}},
                                                    "quantities": [{"name": "available", "quantity": 4}]
                                                },
                                                {
                                                    "location": {"address": {"countryCode": "IE"}},
                                                    "quantities": [{"name": "available", "quantity": 5}]
                                                }
                                            ]
                                        }
                                    },
                                    "inventoryPolicy": "DENY",
                                    "inventoryQuantity": 15,
                                    "media": {"edges": []},
                                    "metafields": {"edges": []},
                                    "price": "23.00",
                                    "selectedOptions": [{"name": "Title", "optionValue": {"id": "gid://shopify/ProductOptionValue/1"}, "value": "Default Title"}],
                                    "sku": "TEST-SKU",
                                    "title": "Default Title",
                                    "contextualPricingGB": {
                                        "price": {"amount": "9.99", "currencyCode": "GBP"},
                                        "compareAtPrice": null
                                    },
                                    "contextualPricingIE": {
                                        "price": {"amount": "12.99", "currencyCode": "EUR"},
                                        "compareAtPrice": null
                                    }
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
