<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Enums\WebhookTopic;
use StatamicRadPack\Shopify\Tests\TestCase;

class WebhooksStatusControllerTest extends TestCase
{
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
    public function returns_empty_webhook_list()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn(new HttpResponse(status: 200, body: $this->shopifyWebhooksResponse()));
        });

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.webhooks.status'));

        $response->assertOk();
        $response->assertJsonStructure(['webhooks', 'expected']);
        $response->assertJsonCount(0, 'webhooks');
        $this->assertNotEmpty($response->json('expected'));
    }

    #[Test]
    public function marks_registered_webhooks_as_expected()
    {
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
        $response->assertJsonCount(1, 'webhooks');

        $webhook = $response->json('webhooks.0');
        $this->assertSame('PRODUCTS_CREATE', $webhook['topic']);
        $this->assertTrue($webhook['expected']);
    }

    #[Test]
    public function marks_unknown_webhooks_as_not_expected()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn(new HttpResponse(status: 200, body: $this->shopifyWebhooksResponse([
                    [
                        'id' => 'gid://shopify/WebhookSubscription/99',
                        'topic' => 'PRODUCTS_CREATE',
                        'endpoint' => ['callbackUrl' => 'https://different-site.com/webhook'],
                        'createdAt' => '2025-01-01T00:00:00Z',
                    ],
                ])));
        });

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.webhooks.status'));

        $response->assertOk();

        $webhook = $response->json('webhooks.0');
        $this->assertFalse($webhook['expected']);
    }

    #[Test]
    public function expected_map_contains_every_topic()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn(new HttpResponse(status: 200, body: $this->shopifyWebhooksResponse()));
        });

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.webhooks.status'));

        $expected = $response->json('expected');

        $this->assertSame(array_map(fn ($topic) => $topic->value, WebhookTopic::cases()), array_keys($expected));
        $this->assertArrayHasKey('ORDERS_CANCELLED', $expected);
        $this->assertArrayHasKey('REFUNDS_CREATE', $expected);
        $this->assertArrayHasKey('INVENTORY_LEVELS_UPDATE', $expected);
    }

    #[Test]
    public function returns_error_when_shopify_is_unreachable()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')
                ->andThrow(new \RuntimeException('Connection refused'));
        });

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.webhooks.status'));

        $response->assertStatus(500);
        $response->assertJsonStructure(['error']);
    }
}
