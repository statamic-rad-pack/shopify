<?php

namespace StatamicRadPack\Shopify\Tests\Unit\Commands;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Shopify\Tests\TestCase;

class ShopifyInstallTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('shopify.url', 'https://test-store.myshopify.com');
        Config::set('app.url', 'https://test-app.com');
    }

    #[Test]
    public function it_has_correct_default_scopes()
    {
        $reflection = new \ReflectionClass(\StatamicRadPack\Shopify\Commands\ShopifyInstall::class);
        $property = $reflection->getProperty('defaultScopes');
        $property->setAccessible(true);
        $defaultScopes = $property->getValue(new \StatamicRadPack\Shopify\Commands\ShopifyInstall());

        $this->assertEquals([
            'write_customers',
            'read_inventory',
            'read_metaobjects',
            'read_orders',
            'read_product_listings',
            'read_products',
            'read_publications',
            'read_translations',
        ], $defaultScopes);
    }

    #[Test]
    public function it_has_all_shopify_scopes_defined()
    {
        $reflection = new \ReflectionClass(\StatamicRadPack\Shopify\Commands\ShopifyInstall::class);
        $property = $reflection->getProperty('availableScopes');
        $property->setAccessible(true);
        $availableScopes = $property->getValue(new \StatamicRadPack\Shopify\Commands\ShopifyInstall());

        $this->assertArrayHasKey('read_products', $availableScopes);
        $this->assertArrayHasKey('write_products', $availableScopes);
        $this->assertArrayHasKey('read_orders', $availableScopes);
        $this->assertArrayHasKey('write_orders', $availableScopes);
        $this->assertGreaterThan(40, count($availableScopes));
    }

    #[Test]
    public function it_has_alphabetically_sorted_scopes()
    {
        $reflection = new \ReflectionClass(\StatamicRadPack\Shopify\Commands\ShopifyInstall::class);
        $property = $reflection->getProperty('availableScopes');
        $property->setAccessible(true);
        $availableScopes = $property->getValue(new \StatamicRadPack\Shopify\Commands\ShopifyInstall());

        $keys = array_keys($availableScopes);
        $sortedKeys = $keys;
        sort($sortedKeys);

        $this->assertEquals($sortedKeys, $keys, 'Scopes should be alphabetically sorted');
    }
}
