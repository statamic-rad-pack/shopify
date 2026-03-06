<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Tests\TestCase;

class WebhooksRegisterCommandTest extends TestCase
{
    private function emptyWebhooksResponse(): string
    {
        return json_encode([
            'data' => [
                'webhookSubscriptions' => [
                    'nodes' => [],
                ],
            ],
        ]);
    }

    private function webhookCreatedResponse(): string
    {
        return json_encode([
            'data' => [
                'webhookSubscriptionCreate' => [
                    'webhookSubscription' => ['id' => 'gid://shopify/WebhookSubscription/1'],
                    'userErrors' => [],
                ],
            ],
        ]);
    }

    #[Test]
    public function registers_all_webhooks_when_none_exist()
    {
        $queryCount = 0;

        $this->mock(Graphql::class, function (MockInterface $mock) use (&$queryCount) {
            // First call: list existing webhooks
            $mock->shouldReceive('query')
                ->withArgs(fn ($q) => str_contains($q['query'], 'webhookSubscriptions'))
                ->once()
                ->andReturn(new HttpResponse(status: 200, body: $this->emptyWebhooksResponse()));

            // Subsequent calls: create mutations (10 topics)
            $mock->shouldReceive('query')
                ->withArgs(fn ($q) => str_contains($q['query'], 'webhookSubscriptionCreate'))
                ->times(10)
                ->andReturn(new HttpResponse(status: 200, body: $this->webhookCreatedResponse()));
        });

        $this->artisan('shopify:webhooks:register')
            ->assertExitCode(0);
    }

    #[Test]
    public function skips_topics_that_are_already_registered()
    {
        $callbackUrl = route('statamic.shopify.webhook.product.create', [], true);

        $existingResponse = json_encode([
            'data' => [
                'webhookSubscriptions' => [
                    'nodes' => [
                        [
                            'id' => 'gid://shopify/WebhookSubscription/1',
                            'topic' => 'PRODUCTS_CREATE',
                            'endpoint' => ['callbackUrl' => $callbackUrl],
                        ],
                    ],
                ],
            ],
        ]);

        $this->mock(Graphql::class, function (MockInterface $mock) use ($existingResponse) {
            $mock->shouldReceive('query')
                ->withArgs(fn ($q) => str_contains($q['query'], 'webhookSubscriptions'))
                ->once()
                ->andReturn(new HttpResponse(status: 200, body: $existingResponse));

            // Only 9 remaining topics should be created
            $mock->shouldReceive('query')
                ->withArgs(fn ($q) => str_contains($q['query'], 'webhookSubscriptionCreate'))
                ->times(9)
                ->andReturn(new HttpResponse(status: 200, body: $this->webhookCreatedResponse()));
        });

        $this->artisan('shopify:webhooks:register')
            ->assertExitCode(0);
    }

    #[Test]
    public function reports_user_errors_from_shopify()
    {
        $errorResponse = json_encode([
            'data' => [
                'webhookSubscriptionCreate' => [
                    'webhookSubscription' => null,
                    'userErrors' => [
                        ['field' => ['callbackUrl'], 'message' => 'Address is invalid'],
                    ],
                ],
            ],
        ]);

        $this->mock(Graphql::class, function (MockInterface $mock) use ($errorResponse) {
            $mock->shouldReceive('query')
                ->withArgs(fn ($q) => str_contains($q['query'], 'webhookSubscriptions'))
                ->once()
                ->andReturn(new HttpResponse(status: 200, body: $this->emptyWebhooksResponse()));

            $mock->shouldReceive('query')
                ->withArgs(fn ($q) => str_contains($q['query'], 'webhookSubscriptionCreate'))
                ->times(10)
                ->andReturn(new HttpResponse(status: 200, body: $errorResponse));
        });

        $this->artisan('shopify:webhooks:register')
            ->expectsOutputToContain('Address is invalid')
            ->assertExitCode(0);
    }

    #[Test]
    public function returns_error_for_unknown_store_in_multi_store_mode()
    {
        config()->set('shopify.multi_store.enabled', true);
        config()->set('shopify.multi_store.mode', 'unified');
        config()->set('shopify.multi_store.stores', [
            'uk' => ['url' => 'uk.myshopify.com', 'admin_token' => 'token'],
        ]);

        $this->artisan('shopify:webhooks:register', ['--store' => 'nonexistent'])
            ->assertExitCode(1);
    }
}
