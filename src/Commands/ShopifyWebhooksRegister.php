<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Shopify\Clients\Graphql;
use Statamic\Support\Arr;
use StatamicRadPack\Shopify\Enums\WebhookTopic;
use StatamicRadPack\Shopify\Support\StoreConfig;

class ShopifyWebhooksRegister extends Command
{
    protected $signature = 'shopify:webhooks:register
                            {--store= : Store handle to register webhooks for (multi-store only)}';

    protected $description = 'Register Shopify webhook subscriptions for all supported topics';

    public function handle(): int
    {
        if (StoreConfig::isMultiStore() && ! StoreConfig::isMarketsMode()) {
            $storeHandle = $this->option('store');

            if ($storeHandle) {
                $store = StoreConfig::findByHandle($storeHandle);

                if (! $store) {
                    $this->error("Store '{$storeHandle}' not found in config.");

                    return 1;
                }

                $this->registerForStore($store);
            } else {
                foreach (array_keys(config('shopify.multi_store.stores', [])) as $handle) {
                    $store = StoreConfig::findByHandle($handle);

                    if ($store) {
                        $this->info("Registering webhooks for store: {$handle}");
                        $this->registerForStore($store);
                    }
                }
            }
        } else {
            $this->registerForStore(null);
        }

        return 0;
    }

    private function registerForStore(?array $storeConfig): void
    {
        $graphql = $storeConfig ? StoreConfig::makeGraphqlClient($storeConfig) : app(Graphql::class);
        $existing = $this->fetchExistingWebhooks($graphql);

        $rows = [];

        foreach (WebhookTopic::cases() as $topic) {
            $callbackUrl = $topic->callbackUrl();

            $alreadyRegistered = collect($existing)->contains(
                fn ($w) => $w['topic'] === $topic->value && $w['callbackUrl'] === $callbackUrl
            );

            if ($alreadyRegistered) {
                $rows[] = [$topic->value, $callbackUrl, 'Already registered'];

                continue;
            }

            $success = $this->createWebhook($graphql, $topic, $callbackUrl);
            $rows[] = [$topic->value, $callbackUrl, $success ? 'Registered' : 'Failed'];
        }

        $this->table(['Topic', 'Callback URL', 'Status'], $rows);
    }

    private function fetchExistingWebhooks(Graphql $graphql): array
    {
        $query = <<<'QUERY'
        {
          webhookSubscriptions(first: 100) {
            nodes {
              id
              topic
              endpoint {
                ... on WebhookHttpEndpoint {
                  callbackUrl
                }
              }
            }
          }
        }
        QUERY;

        $response = $graphql->query(['query' => $query]);
        $nodes = Arr::get($response->getDecodedBody(), 'data.webhookSubscriptions.nodes', []);

        return collect($nodes)->map(fn ($node) => [
            'id' => $node['id'],
            'topic' => $node['topic'],
            'callbackUrl' => Arr::get($node, 'endpoint.callbackUrl', ''),
        ])->all();
    }

    private function createWebhook(Graphql $graphql, WebhookTopic $topic, string $callbackUrl): bool
    {
        $mutation = <<<'QUERY'
        mutation webhookSubscriptionCreate($topic: WebhookSubscriptionTopic!, $webhookSubscription: WebhookSubscriptionInput!) {
          webhookSubscriptionCreate(topic: $topic, webhookSubscription: $webhookSubscription) {
            webhookSubscription {
              id
            }
            userErrors {
              field
              message
            }
          }
        }
        QUERY;

        $response = $graphql->query([
            'query' => $mutation,
            'variables' => [
                'topic' => $topic->value,
                'webhookSubscription' => [
                    'callbackUrl' => $callbackUrl,
                    'format' => 'JSON',
                ],
            ],
        ]);

        $errors = Arr::get($response->getDecodedBody(), 'data.webhookSubscriptionCreate.userErrors', []);

        if ($errors) {
            foreach ($errors as $error) {
                $this->error(($error['field'] ? implode(', ', $error['field']).': ' : '').$error['message']);
            }

            return false;
        }

        return (bool) Arr::get($response->getDecodedBody(), 'data.webhookSubscriptionCreate.webhookSubscription.id');
    }
}
