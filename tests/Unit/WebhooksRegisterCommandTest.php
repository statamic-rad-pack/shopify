<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Enums\WebhookTopic;
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

    /**
     * Every supported topic registered at its expected URL, plus the given extra subscriptions.
     */
    private function existingWebhooksResponse(array $extra = []): string
    {
        $nodes = collect(WebhookTopic::cases())->map(fn ($topic) => [
            'id' => 'gid://shopify/WebhookSubscription/'.$topic->value,
            'topic' => $topic->value,
            'endpoint' => ['callbackUrl' => $topic->callbackUrl()],
        ])->all();

        foreach ($extra as $id => [$topic, $url]) {
            $nodes[] = ['id' => $id, 'topic' => $topic, 'endpoint' => ['callbackUrl' => $url]];
        }

        return json_encode(['data' => ['webhookSubscriptions' => ['nodes' => $nodes]]]);
    }

    private function staleWebhooks(): array
    {
        return [
            'gid://shopify/WebhookSubscription/old-domain' => ['PRODUCTS_UPDATE', 'https://old-site.test/!/shopify/webhook/product/update'],
            'gid://shopify/WebhookSubscription/removed-topic' => ['CARTS_UPDATE', WebhookTopic::ProductsUpdate->callbackUrl()],
            'gid://shopify/WebhookSubscription/duplicate' => ['ORDERS_CREATE', WebhookTopic::OrdersCreate->callbackUrl()],
            'gid://shopify/WebhookSubscription/custom' => ['ORDERS_PAID', url('/my-own-webhooks/orders-paid')],
        ];
    }

    private function mockPrune(string $existing, array &$deleted): void
    {
        $this->mock(Graphql::class, function (MockInterface $mock) use ($existing, &$deleted) {
            $mock->shouldReceive('query')
                ->withArgs(fn ($q) => str_contains($q['query'], 'webhookSubscriptions'))
                ->andReturn(new HttpResponse(status: 200, body: $existing));

            $mock->shouldReceive('query')
                ->withArgs(fn ($q) => str_contains($q['query'], 'webhookSubscriptionDelete'))
                ->andReturnUsing(function ($q) use (&$deleted) {
                    $deleted[] = $q['variables']['id'];

                    return new HttpResponse(status: 200, body: json_encode(['data' => ['webhookSubscriptionDelete' => [
                        'deletedWebhookSubscriptionId' => $q['variables']['id'],
                        'userErrors' => [],
                    ]]]));
                });
        });
    }

    #[Test]
    public function prunes_old_urls_removed_topics_and_duplicates_but_not_other_webhooks()
    {
        $deleted = [];
        $this->mockPrune($this->existingWebhooksResponse($this->staleWebhooks()), $deleted);

        $this->artisan('shopify:webhooks:register', ['--prune' => true, '--force' => true])
            ->expectsOutputToContain('Another site (old-site.test)')
            ->expectsOutputToContain('Topic no longer used')
            ->expectsOutputToContain('Duplicate')
            ->expectsOutputToContain('Deleted 3 webhook subscription(s).')
            ->assertSuccessful();

        $this->assertEqualsCanonicalizing([
            'gid://shopify/WebhookSubscription/old-domain',
            'gid://shopify/WebhookSubscription/removed-topic',
            'gid://shopify/WebhookSubscription/duplicate',
        ], $deleted);
    }

    #[Test]
    public function asks_before_pruning_and_deletes_nothing_when_declined()
    {
        $deleted = [];
        $this->mockPrune($this->existingWebhooksResponse($this->staleWebhooks()), $deleted);

        $this->artisan('shopify:webhooks:register', ['--prune' => true])
            ->expectsOutputToContain('it will stop receiving webhooks')
            ->expectsConfirmation('Delete these 3 webhook subscription(s)?', 'no')
            ->expectsOutput('Nothing was deleted.')
            ->assertSuccessful();

        $this->assertSame([], $deleted);
    }

    #[Test]
    public function reports_when_there_is_nothing_to_prune()
    {
        $deleted = [];
        $this->mockPrune($this->existingWebhooksResponse(), $deleted);

        $this->artisan('shopify:webhooks:register', ['--prune' => true])
            ->expectsOutput('No webhook subscriptions to prune.')
            ->assertSuccessful();

        $this->assertSame([], $deleted);
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

            // Subsequent calls: a create mutation per topic
            $mock->shouldReceive('query')
                ->withArgs(fn ($q) => str_contains($q['query'], 'webhookSubscriptionCreate'))
                ->times(count(WebhookTopic::cases()))
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

            // Only the remaining topics should be created
            $mock->shouldReceive('query')
                ->withArgs(fn ($q) => str_contains($q['query'], 'webhookSubscriptionCreate'))
                ->times(count(WebhookTopic::cases()) - 1)
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
                ->times(count(WebhookTopic::cases()))
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
