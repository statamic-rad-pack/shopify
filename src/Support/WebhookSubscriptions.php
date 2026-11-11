<?php

namespace StatamicRadPack\Shopify\Support;

use Illuminate\Support\Facades\Cache;
use Statamic\Support\Arr;
use StatamicRadPack\Shopify\Clients\Graphql;

class WebhookSubscriptions
{
    /**
     * The webhook subscriptions registered in Shopify for the given client's store.
     *
     * @return array<int, array{id: string, topic: string, callbackUrl: string, createdAt: ?string}>
     */
    public static function fetch(Graphql $graphql): array
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
              createdAt
            }
          }
        }
        QUERY;

        $response = $graphql->query(['query' => $query]);

        return collect(Arr::get($response->getDecodedBody(), 'data.webhookSubscriptions.nodes', []))
            ->map(fn ($node) => [
                'id' => $node['id'],
                'topic' => $node['topic'],
                'callbackUrl' => Arr::get($node, 'endpoint.callbackUrl', ''),
                'createdAt' => $node['createdAt'] ?? null,
            ])
            ->all();
    }

    /**
     * Record that a webhook for the topic (e.g. PRODUCTS_UPDATE) was just received.
     */
    public static function recordReceived(string $topic, ?string $storeHandle = null): void
    {
        Cache::forever(static::lastReceivedKey($topic, $storeHandle), now()->toIso8601String());
    }

    public static function lastReceived(string $topic, ?string $storeHandle = null): ?string
    {
        return Cache::get(static::lastReceivedKey($topic, $storeHandle));
    }

    private static function lastReceivedKey(string $topic, ?string $storeHandle): string
    {
        return $storeHandle
            ? "shopify::webhook_last_received::{$storeHandle}::{$topic}"
            : "shopify::webhook_last_received::{$topic}";
    }
}
