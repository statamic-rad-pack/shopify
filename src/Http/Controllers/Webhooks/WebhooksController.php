<?php

namespace Jackabox\Shopify\Http\Controllers\Webhooks;

use Statamic\Http\Controllers\CP\CpController;

class WebhooksController extends CpController
{
    /**
     * Verify integrity
     *
     * @param $data
     * @param $hmac_header
     * @return bool
     */
    protected function verify($data, $hmac_header): bool
    {
        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, config('shopify.webhook_secret'), true));

        return hash_equals($hmac_header, $calculated_hmac);
    }
}
