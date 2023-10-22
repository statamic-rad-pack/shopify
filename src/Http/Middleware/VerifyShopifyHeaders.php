<?php

namespace StatamicRadPack\Shopify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyShopifyHeaders
{
    /**
     * before response sent back to browser
     */
    public function handle(Request $request, Closure $next)
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $verified = $this->verify($data, $hmacHeader);

        if (! $verified) {
            return response()->json(['error' => true], 403);
        }

        return $next($request);
    }

    /**
     * Verify integrity
     */
    protected function verify($data, $hmacHeader): bool
    {
        if (config('shopify.ignore_webhook_integrity_check', false)) {
            return true;
        }

        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, config('shopify.webhook_secret'), true));

        return hash_equals($hmacHeader, $calculatedHmac);
    }
}
