<?php

namespace StatamicRadPack\Shopify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use StatamicRadPack\Shopify\Support\StoreConfig;

class VerifyShopifyHeaders
{
    /**
     * before response sent back to browser
     */
    public function handle(Request $request, Closure $next)
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $domain = $request->header('X-Shopify-Shop-Domain');

        // In multi-store mode, resolve and validate the store by domain header
        if (StoreConfig::isMultiStore() && $domain) {
            $store = StoreConfig::findByDomain($domain);

            if (! $store) {
                return response()->json(['error' => true], 403);
            }

            $request->attributes->set('shopify_store_handle', $store['handle']);
        }

        $verified = $this->verify($data, $hmacHeader, $domain);

        if (! $verified) {
            return response()->json(['error' => true], 403);
        }

        $this->recordLastReceived($request);

        return $next($request);
    }

    protected function recordLastReceived(Request $request): void
    {
        $rawTopic = $request->header('X-Shopify-Topic');

        if (! $rawTopic) {
            return;
        }

        $topic = str_replace('/', '_', strtoupper($rawTopic));
        $storeHandle = $request->attributes->get('shopify_store_handle');

        $cacheKey = $storeHandle
            ? "shopify::webhook_last_received::{$storeHandle}::{$topic}"
            : "shopify::webhook_last_received::{$topic}";

        Cache::forever($cacheKey, now()->toIso8601String());
    }

    /**
     * Verify integrity
     */
    protected function verify($data, $hmacHeader, ?string $domain = null): bool
    {
        if (config('shopify.ignore_webhook_integrity_check', false)) {
            return true;
        }

        $secret = config('shopify.webhook_secret');

        if (StoreConfig::isMultiStore() && $domain) {
            $store = StoreConfig::findByDomain($domain);
            if ($store) {
                $secret = $store['webhook_secret'] ?? $secret;
            }
        }

        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));

        return hash_equals($hmacHeader, $calculatedHmac);
    }
}
