<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Statamic\Facades\User;
use StatamicRadPack\Shopify\Tests\TestCase;

class WebhookLastReceivedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('shopify.ignore_webhook_integrity_check', true);
    }

    private function actingAsSuperUser()
    {
        $user = User::make()->email('admin@example.com')->makeSuper()->save();

        return $this->actingAs($user);
    }

    private function shopifyWebhooksResponse(array $nodes = []): string
    {
        return json_encode([
            'data' => [
                'webhookSubscriptions' => [
                    'nodes' => $nodes,
                ],
            ],
        ]);
    }

    #[Test]
    public function records_last_received_timestamp_on_webhook_request()
    {
        Cache::flush();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: '{}'));
        });

        $this->postJson('/!/shopify/webhook/product/create', ['id' => 1], [
            'X-Shopify-Topic' => 'products/create',
        ]);

        $this->assertNotNull(Cache::get('shopify::webhook_last_received::PRODUCTS_CREATE'));
    }

    #[Test]
    public function normalises_topic_header_to_enum_format()
    {
        Cache::flush();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: '{}'));
        });

        $this->postJson('/!/shopify/webhook/collection/update', ['id' => 1], [
            'X-Shopify-Topic' => 'collections/update',
        ]);

        $this->assertNotNull(Cache::get('shopify::webhook_last_received::COLLECTIONS_UPDATE'));
    }

    #[Test]
    public function does_not_record_timestamp_when_verification_fails()
    {
        Cache::flush();
        config()->set('shopify.ignore_webhook_integrity_check', false);
        config()->set('shopify.webhook_secret', 'secret');

        $this->postJson('/!/shopify/webhook/product/create', ['id' => 1], [
            'X-Shopify-Topic' => 'products/create',
            'X-Shopify-Hmac-Sha256' => 'invalid',
        ]);

        $this->assertNull(Cache::get('shopify::webhook_last_received::PRODUCTS_CREATE'));
    }

    #[Test]
    public function records_timestamp_per_store_in_multi_store_mode()
    {
        Cache::flush();

        config(['shopify.multi_store' => [
            'enabled' => true,
            'mode' => 'unified',
            'stores' => [
                'uk' => [
                    'url' => 'uk.myshopify.com',
                    'webhook_secret' => 'secret',
                ],
            ],
        ]]);

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: '{}'));
        });

        $this->postJson('/!/shopify/webhook/product/update', ['id' => 1], [
            'X-Shopify-Topic' => 'products/update',
            'X-Shopify-Shop-Domain' => 'uk.myshopify.com',
        ]);

        $this->assertNotNull(Cache::get('shopify::webhook_last_received::uk::PRODUCTS_UPDATE'));
        $this->assertNull(Cache::get('shopify::webhook_last_received::PRODUCTS_UPDATE'));
    }

    #[Test]
    public function status_controller_includes_last_received_at_for_each_webhook()
    {
        $callbackUrl = route('statamic.shopify.webhook.product.create', [], true);

        Cache::forever('shopify::webhook_last_received::PRODUCTS_CREATE', '2025-06-01T12:00:00+00:00');

        $this->mock(Graphql::class, function (MockInterface $mock) use ($callbackUrl) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn(new HttpResponse(status: 200, body: $this->shopifyWebhooksResponse([
                    [
                        'id' => 'gid://shopify/WebhookSubscription/1',
                        'topic' => 'PRODUCTS_CREATE',
                        'endpoint' => ['callbackUrl' => $callbackUrl],
                        'createdAt' => '2025-01-01T00:00:00Z',
                    ],
                ])));
        });

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.webhooks.status'));

        $response->assertOk();
        $this->assertSame('2025-06-01T12:00:00+00:00', $response->json('webhooks.0.last_received_at'));
    }

    #[Test]
    public function status_controller_returns_null_last_received_at_when_never_fired()
    {
        Cache::flush();

        $callbackUrl = route('statamic.shopify.webhook.product.create', [], true);

        $this->mock(Graphql::class, function (MockInterface $mock) use ($callbackUrl) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn(new HttpResponse(status: 200, body: $this->shopifyWebhooksResponse([
                    [
                        'id' => 'gid://shopify/WebhookSubscription/1',
                        'topic' => 'PRODUCTS_CREATE',
                        'endpoint' => ['callbackUrl' => $callbackUrl],
                        'createdAt' => '2025-01-01T00:00:00Z',
                    ],
                ])));
        });

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.webhooks.status'));

        $response->assertOk();
        $this->assertNull($response->json('webhooks.0.last_received_at'));
    }

    #[Test]
    public function status_controller_uses_store_scoped_cache_key_in_multi_store_mode()
    {
        Cache::flush();

        config(['shopify.multi_store' => [
            'enabled' => true,
            'mode' => 'unified',
            'stores' => [
                'uk' => ['url' => 'uk.myshopify.com', 'admin_token' => 'tok'],
            ],
        ]]);

        $callbackUrl = route('statamic.shopify.webhook.product.create', [], true);

        Cache::forever('shopify::webhook_last_received::uk::PRODUCTS_CREATE', '2025-06-01T12:00:00+00:00');

        $this->app->instance('shopify.graphql.uk', tap($this->mock(Graphql::class), function (MockInterface $mock) use ($callbackUrl) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn(new HttpResponse(status: 200, body: $this->shopifyWebhooksResponse([
                    [
                        'id' => 'gid://shopify/WebhookSubscription/1',
                        'topic' => 'PRODUCTS_CREATE',
                        'endpoint' => ['callbackUrl' => $callbackUrl],
                        'createdAt' => '2025-01-01T00:00:00Z',
                    ],
                ])));
        }));

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.webhooks.status').'?store=uk');

        $response->assertOk();
        $this->assertSame('2025-06-01T12:00:00+00:00', $response->json('webhooks.0.last_received_at'));
    }
}
