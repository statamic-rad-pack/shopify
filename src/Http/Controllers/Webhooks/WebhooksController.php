<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Statamic\Http\Controllers\CP\CpController;

class WebhooksController extends CpController
{
    /**
     * Verify integrity
     */
    protected function verify($data, $hmac_header): bool
    {
        if (config('shopify.ignore_webhook_integrity_check', false)) {
            return true;
        }

        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, config('shopify.webhook_secret'), true));

        return hash_equals($hmac_header, $calculated_hmac);
    }
}
