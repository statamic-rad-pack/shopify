<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Shopify\Enums\WebhookTopic;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Support\WebhookSubscriptions;

class WebhooksStatusController extends CpController
{
    public function index(Request $request)
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }

        $storeHandle = $request->get('store');

        try {
            $registered = WebhookSubscriptions::fetch(StoreConfig::clientFor($storeHandle));
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Could not connect to Shopify: '.$e->getMessage()], 500);
        }

        $expected = collect(WebhookTopic::cases())
            ->mapWithKeys(fn ($topic) => [$topic->value => $topic->callbackUrl()])
            ->all();

        $webhooks = collect($registered)->map(fn ($webhook) => array_merge($webhook, [
            'expected' => isset($expected[$webhook['topic']])
                && $webhook['callbackUrl'] === $expected[$webhook['topic']],
            'last_received_at' => WebhookSubscriptions::lastReceived($webhook['topic'], $storeHandle),
        ]))->all();

        return response()->json([
            'webhooks' => $webhooks,
            'expected' => $expected,
        ]);
    }
}
