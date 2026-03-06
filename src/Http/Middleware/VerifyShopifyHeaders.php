<?php

namespace StatamicRadPack\Shopify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        return $next($request);
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
