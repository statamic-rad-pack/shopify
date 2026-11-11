<?php

namespace StatamicRadPack\Shopify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Support\WebhookSubscriptions;

class VerifyShopifyHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('shopify.webhook_secret');

        // In multi-store mode, resolve the store by domain header and use its secret
        if (StoreConfig::isMultiStore() && ($domain = $request->header('X-Shopify-Shop-Domain'))) {
            if (! $store = StoreConfig::findByDomain($domain)) {
                return response()->json(['error' => true], 403);
            }

            $request->attributes->set('shopify_store_handle', $store['handle']);
            $secret = $store['webhook_secret'] ?? $secret;
        }

        if (! $this->verify($request->getContent(), $request->header('X-Shopify-Hmac-Sha256'), $secret)) {
            return response()->json(['error' => true], 403);
        }

        $this->recordLastReceived($request);

        // Shopify retries deliveries it doesn't see succeed, and can deliver the same
        // webhook more than once, so skip any delivery we've already handled.
        $deliveryKey = $this->deliveryKey($request);

        if ($deliveryKey && Cache::has($deliveryKey)) {
            return response()->json(['message' => 'Webhook already processed'], 200);
        }

        $response = $next($request);

        if ($deliveryKey && $response->isSuccessful()) {
            Cache::put($deliveryKey, true, now()->addDay());
        }

        return $response;
    }

    protected function recordLastReceived(Request $request): void
    {
        if (! $rawTopic = $request->header('X-Shopify-Topic')) {
            return;
        }

        WebhookSubscriptions::recordReceived(
            str_replace('/', '_', strtoupper($rawTopic)),
            $request->attributes->get('shopify_store_handle')
        );
    }

    protected function deliveryKey(Request $request): ?string
    {
        if (! $webhookId = $request->header('X-Shopify-Webhook-Id')) {
            return null;
        }

        return 'shopify::webhook_processed::'.$request->header('X-Shopify-Topic').'::'.$webhookId;
    }

    protected function verify(string $data, ?string $hmacHeader, ?string $secret): bool
    {
        if (config('shopify.ignore_webhook_integrity_check', false)) {
            return true;
        }

        if (! $hmacHeader || ! $secret) {
            return false;
        }

        return hash_equals($hmacHeader, base64_encode(hash_hmac('sha256', $data, $secret, true)));
    }
}
