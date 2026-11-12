<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Statamic\Support\Arr;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Enums\WebhookTopic;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Support\WebhookSubscriptions;

class ShopifyWebhooksRegister extends Command
{
    protected $signature = 'shopify:webhooks:register
                            {--store= : Store handle to register webhooks for (multi-store only)}
                            {--prune : Also remove this app\'s webhook subscriptions the addon no longer uses (old URLs, removed topics, duplicates)}
                            {--force : Prune without asking for confirmation}';

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
        $graphql = StoreConfig::clientFor($storeConfig['handle'] ?? null);
        $existing = WebhookSubscriptions::fetch($graphql);

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

        if ($this->option('prune')) {
            $this->prune($graphql, $existing);
        }
    }

    /**
     * Remove subscriptions pointing at the addon's webhook routes that aren't the
     * expected URL for a supported topic, or that duplicate one. Subscriptions
     * elsewhere (e.g. a site's own webhook routes) are left alone, and Shopify
     * only lists this app's subscriptions, so other apps' are never touched.
     */
    private function prune(Graphql $graphql, array $existing): void
    {
        $expected = collect(WebhookTopic::cases())->mapWithKeys(fn ($topic) => [$topic->value => $topic->callbackUrl()]);
        $webhookPath = $this->webhookPath();
        $host = parse_url(WebhookTopic::ProductsUpdate->callbackUrl(), PHP_URL_HOST);
        $kept = [];

        $stale = collect($existing)
            ->filter(fn ($webhook) => str_starts_with((string) parse_url($webhook['callbackUrl'], PHP_URL_PATH), $webhookPath))
            ->map(function ($webhook) use ($expected, $host, &$kept) {
                $key = $webhook['topic'].' '.$webhook['callbackUrl'];

                if ($expected->get($webhook['topic']) === $webhook['callbackUrl'] && ! isset($kept[$key])) {
                    $kept[$key] = true;

                    return null;
                }

                $webhookHost = parse_url($webhook['callbackUrl'], PHP_URL_HOST);

                return $webhook + ['reason' => match (true) {
                    ! $expected->has($webhook['topic']) => 'Topic no longer used',
                    $webhookHost !== $host => "Another site ({$webhookHost})",
                    $expected->get($webhook['topic']) !== $webhook['callbackUrl'] => 'Old URL',
                    default => 'Duplicate',
                }];
            })
            ->filter()
            ->values();

        if ($stale->isEmpty()) {
            $this->info('No webhook subscriptions to prune.');

            return;
        }

        $this->table(['Topic', 'Callback URL', 'Reason'], $stale->map(fn ($webhook) => [$webhook['topic'], $webhook['callbackUrl'], $webhook['reason']])->all());

        if ($stale->contains(fn ($webhook) => str_starts_with($webhook['reason'], 'Another site'))) {
            $this->warn('Some of these point at another site. If that site uses the same Shopify app (e.g. staging and production), it will stop receiving webhooks.');
        }

        if (! $this->option('force') && ! $this->confirm("Delete these {$stale->count()} webhook subscription(s)?", false)) {
            $this->line('Nothing was deleted.');

            return;
        }

        $deleted = $stale->filter(fn ($webhook) => $this->deleteWebhook($graphql, $webhook['id']))->count();

        $this->info("Deleted {$deleted} webhook subscription(s).");
    }

    /**
     * The path all of the addon's webhook routes share, e.g. /!/shopify/webhook/.
     */
    private function webhookPath(): string
    {
        $path = (string) parse_url(WebhookTopic::ProductsUpdate->callbackUrl(), PHP_URL_PATH);

        return substr($path, 0, strrpos($path, '/webhook/') + strlen('/webhook/'));
    }

    private function deleteWebhook(Graphql $graphql, string $id): bool
    {
        $mutation = <<<'QUERY'
        mutation webhookSubscriptionDelete($id: ID!) {
          webhookSubscriptionDelete(id: $id) {
            deletedWebhookSubscriptionId
            userErrors {
              field
              message
            }
          }
        }
        QUERY;

        $response = $graphql->query(['query' => $mutation, 'variables' => ['id' => $id]]);

        if ($error = $response->firstError('webhookSubscriptionDelete')) {
            $this->error("Could not delete {$id}: {$error}");

            return false;
        }

        return (bool) Arr::get($response->getDecodedBody(), 'data.webhookSubscriptionDelete.deletedWebhookSubscriptionId');
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
