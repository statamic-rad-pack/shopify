<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Tests\TestCase;

class StoreConfigTest extends TestCase
{
    private function multiStoreConfig(string $mode = 'unified'): array
    {
        return [
            'enabled' => true,
            'mode' => $mode,
            'stores' => [
                'uk' => ['url' => 'uk-store.myshopify.com', 'admin_token' => 'uk-token'],
            ],
        ];
    }

    #[Test]
    public function client_for_returns_the_store_client_in_multi_store_mode()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());

        $default = Mockery::mock(Graphql::class);
        $uk = Mockery::mock(Graphql::class);
        $this->app->instance(Graphql::class, $default);
        $this->app->instance('shopify.graphql.uk', $uk);

        $this->assertSame($uk, StoreConfig::clientFor('uk'));
        $this->assertSame($default, StoreConfig::clientFor(null));
        $this->assertSame($default, StoreConfig::clientFor('unknown'));
    }

    #[Test]
    public function client_for_returns_the_default_client_when_multi_store_is_off()
    {
        $default = Mockery::mock(Graphql::class);
        $this->app->instance(Graphql::class, $default);
        $this->app->instance('shopify.graphql.uk', Mockery::mock(Graphql::class));

        $this->assertSame($default, StoreConfig::clientFor('uk'));
    }

    #[Test]
    public function reports_the_multi_store_mode()
    {
        $this->assertFalse(StoreConfig::isUnifiedMode());
        $this->assertFalse(StoreConfig::isLocalizedMode());

        config()->set('shopify.multi_store', $this->multiStoreConfig('localized'));
        $this->assertTrue(StoreConfig::isLocalizedMode());
        $this->assertFalse(StoreConfig::isUnifiedMode());

        config()->set('shopify.multi_store', $this->multiStoreConfig('unified'));
        $this->assertTrue(StoreConfig::isUnifiedMode());
    }
}
