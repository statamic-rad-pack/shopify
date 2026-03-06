<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\Request;
use Shopify\Clients\Graphql;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;
use StatamicRadPack\Shopify\Enums\WebhookTopic;
use StatamicRadPack\Shopify\Support\StoreConfig;

class WebhooksStatusController extends CpController
{
    public function index(Request $request)
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }

        $storeHandle = $request->get('store');

        try {
            if ($storeHandle && StoreConfig::isMultiStore()) {
                $storeConfig = StoreConfig::findByHandle($storeHandle);
                $graphql = $storeConfig ? StoreConfig::makeGraphqlClient($storeConfig) : app(Graphql::class);
            } else {
                $graphql = app(Graphql::class);
            }

            $registered = $this->fetchWebhooks($graphql);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Could not connect to Shopify: '.$e->getMessage()], 500);
        }

        $expected = collect(WebhookTopic::cases())
            ->mapWithKeys(fn ($topic) => [$topic->value => $topic->callbackUrl()])
            ->all();

        $webhooks = collect($registered)->map(fn ($webhook) => array_merge($webhook, [
            'expected' => isset($expected[$webhook['topic']])
                && $webhook['callbackUrl'] === $expected[$webhook['topic']],
        ]))->all();

        return response()->json([
            'webhooks' => $webhooks,
            'expected' => $expected,
        ]);
    }

    private function fetchWebhooks(Graphql $graphql): array
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
        $nodes = Arr::get($response->getDecodedBody(), 'data.webhookSubscriptions.nodes', []);

        return collect($nodes)->map(fn ($node) => [
            'id' => $node['id'],
            'topic' => $node['topic'],
            'callbackUrl' => Arr::get($node, 'endpoint.callbackUrl', ''),
            'createdAt' => $node['createdAt'],
        ])->all();
    }
}
