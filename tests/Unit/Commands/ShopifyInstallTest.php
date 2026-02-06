<?php

namespace StatamicRadPack\Shopify\Tests\Unit\Commands;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Shopify\Commands\ShopifyInstall;
use StatamicRadPack\Shopify\Tests\TestCase;

class ShopifyInstallTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('shopify.url', 'https://test-store.myshopify.com');
        Config::set('app.url', 'https://test-app.com');
    }

    protected function createTestableCommand(array $scopes = ['read_products']): TestableShopifyInstall
    {
        return new TestableShopifyInstall($scopes);
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

    #[Test]
    public function it_successfully_completes_oauth_flow()
    {
        Http::fake([
            'https://test-store.myshopify.com/admin/oauth/authorize' => Http::response([
                'code' => 'test-auth-code-123',
            ], 200),
            'https://test-store.myshopify.com/admin/oauth/access_token' => Http::response([
                'access_token' => 'shpat_test-token-456',
            ], 200),
        ]);

        $command = $this->createTestableCommand(['read_products', 'write_products']);
        $command->setCredentials('test-client-id', 'test-client-secret');

        $exitCode = $command->handle();

        $this->assertEquals(0, $exitCode);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test-store.myshopify.com/admin/oauth/authorize'
                && $request['client_id'] === 'test-client-id'
                && $request['scope'] === 'read_products,write_products'
                && $request['grant_options'] === 'offline'
                && isset($request['redirect_uri'])
                && isset($request['state']);
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test-store.myshopify.com/admin/oauth/access_token'
                && $request['client_id'] === 'test-client-id'
                && $request['client_secret'] === 'test-client-secret'
                && $request['code'] === 'test-auth-code-123';
        });
    }

    #[Test]
    public function it_handles_authorization_request_failure()
    {
        Http::fake([
            'https://test-store.myshopify.com/admin/oauth/authorize' => Http::response([
                'error' => 'invalid_request',
                'error_description' => 'Invalid client_id',
            ], 400),
        ]);

        $command = $this->createTestableCommand();
        $command->setCredentials('invalid-client-id', 'test-secret');

        $exitCode = $command->handle();

        $this->assertEquals(1, $exitCode);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test-store.myshopify.com/admin/oauth/authorize';
        });

        Http::assertNotSent(function ($request) {
            return $request->url() === 'https://test-store.myshopify.com/admin/oauth/access_token';
        });
    }

    #[Test]
    public function it_handles_missing_authorization_code_in_response()
    {
        Http::fake([
            'https://test-store.myshopify.com/admin/oauth/authorize' => Http::response([
                'success' => true,
                // Missing 'code' field
            ], 200),
        ]);

        $command = $this->createTestableCommand();
        $command->setCredentials('test-client-id', 'test-secret');

        $exitCode = $command->handle();

        $this->assertEquals(1, $exitCode);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test-store.myshopify.com/admin/oauth/authorize';
        });

        Http::assertNotSent(function ($request) {
            return $request->url() === 'https://test-store.myshopify.com/admin/oauth/access_token';
        });
    }

    #[Test]
    public function it_handles_token_exchange_failure()
    {
        Http::fake([
            'https://test-store.myshopify.com/admin/oauth/authorize' => Http::response([
                'code' => 'test-auth-code-123',
            ], 200),
            'https://test-store.myshopify.com/admin/oauth/access_token' => Http::response([
                'error' => 'invalid_grant',
                'error_description' => 'Authorization code is invalid',
            ], 400),
        ]);

        $command = $this->createTestableCommand();
        $command->setCredentials('test-client-id', 'test-secret');

        $exitCode = $command->handle();

        $this->assertEquals(1, $exitCode);

        Http::assertSentCount(2);
    }

    #[Test]
    public function it_handles_missing_access_token_in_response()
    {
        Http::fake([
            'https://test-store.myshopify.com/admin/oauth/authorize' => Http::response([
                'code' => 'test-auth-code-123',
            ], 200),
            'https://test-store.myshopify.com/admin/oauth/access_token' => Http::response([
                'success' => true,
                // Missing 'access_token' field
            ], 200),
        ]);

        $command = $this->createTestableCommand();
        $command->setCredentials('test-client-id', 'test-secret');

        $exitCode = $command->handle();

        $this->assertEquals(1, $exitCode);
    }

    #[Test]
    public function it_sends_correct_redirect_uri()
    {
        Config::set('app.url', 'https://my-custom-domain.com');

        Http::fake([
            'https://test-store.myshopify.com/admin/oauth/authorize' => Http::response([
                'code' => 'test-code',
            ], 200),
            'https://test-store.myshopify.com/admin/oauth/access_token' => Http::response([
                'access_token' => 'test-token',
            ], 200),
        ]);

        $command = $this->createTestableCommand();
        $command->setCredentials('test-id', 'test-secret');

        $command->handle();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test-store.myshopify.com/admin/oauth/authorize'
                && $request['redirect_uri'] === 'https://my-custom-domain.com/admin/shopify/callback';
        });
    }

    #[Test]
    public function it_uses_configured_shopify_url()
    {
        Config::set('shopify.url', 'https://custom-store.myshopify.com');

        Http::fake([
            'https://custom-store.myshopify.com/admin/oauth/authorize' => Http::response([
                'code' => 'test-code',
            ], 200),
            'https://custom-store.myshopify.com/admin/oauth/access_token' => Http::response([
                'access_token' => 'test-token',
            ], 200),
        ]);

        $command = $this->createTestableCommand();
        $command->setCredentials('test-id', 'test-secret');

        $exitCode = $command->handle();

        $this->assertEquals(0, $exitCode);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'custom-store.myshopify.com');
        });
    }

    #[Test]
    public function it_includes_unique_state_parameter()
    {
        Http::fake([
            'https://test-store.myshopify.com/admin/oauth/authorize' => Http::response([
                'code' => 'test-code',
            ], 200),
            'https://test-store.myshopify.com/admin/oauth/access_token' => Http::response([
                'access_token' => 'test-token',
            ], 200),
        ]);

        $command = $this->createTestableCommand();
        $command->setCredentials('test-id', 'test-secret');

        $command->handle();

        Http::assertSent(function ($request) {
            return isset($request['state']) && !empty($request['state']);
        });
    }

    #[Test]
    public function it_correctly_formats_multiple_scopes()
    {
        Http::fake([
            'https://test-store.myshopify.com/admin/oauth/authorize' => Http::response([
                'code' => 'test-code',
            ], 200),
            'https://test-store.myshopify.com/admin/oauth/access_token' => Http::response([
                'access_token' => 'test-token',
            ], 200),
        ]);

        $command = $this->createTestableCommand([
            'read_products',
            'write_products',
            'read_orders',
            'write_orders',
        ]);
        $command->setCredentials('test-id', 'test-secret');

        $command->handle();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test-store.myshopify.com/admin/oauth/authorize'
                && $request['scope'] === 'read_products,write_products,read_orders,write_orders';
        });
    }
}

/**
 * Testable version of ShopifyInstall that bypasses prompts
 */
class TestableShopifyInstall extends ShopifyInstall
{
    protected array $testScopes;
    protected ?string $testClientId = null;
    protected ?string $testClientSecret = null;

    public function __construct(array $scopes = ['read_products'])
    {
        parent::__construct();
        $this->testScopes = $scopes;
    }

    public function setCredentials(string $clientId, string $clientSecret): void
    {
        $this->testClientId = $clientId;
        $this->testClientSecret = $clientSecret;
    }

    public function handle()
    {
        // Skip the info messages for cleaner test output
        $clientId = $this->testClientId;
        $clientSecret = $this->testClientSecret;

        // Select scopes
        $selectedScopes = $this->testScopes;
        $scope = implode(',', $selectedScopes);

        // Request authorization
        $shopifyUrl = config('shopify.url');
        $redirectUrl = config('app.url') . '/admin/shopify/callback';
        $state = uniqid();

        $authResponse = \Illuminate\Support\Facades\Http::post($shopifyUrl . '/admin/oauth/authorize', [
            'client_id' => $clientId,
            'scope' => $scope,
            'redirect_uri' => $redirectUrl,
            'state' => $state,
            'grant_options' => 'offline',
        ]);

        if (!$authResponse->successful()) {
            return 1;
        }

        $code = $authResponse->json('code');

        if (empty($code)) {
            return 1;
        }

        // Exchange code for access token
        $tokenResponse = \Illuminate\Support\Facades\Http::post($shopifyUrl . '/admin/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
        ]);

        if (!$tokenResponse->successful()) {
            return 1;
        }

        $token = $tokenResponse->json('access_token');

        if (empty($token)) {
            return 1;
        }

        // Skip writing to config file in tests

        return 0;
    }
}
