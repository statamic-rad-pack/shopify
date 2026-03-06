<?php

namespace StatamicRadPack\Shopify\Traits;

use Shopify\Clients\Graphql;
use Statamic\Support\Arr;

trait ThrottlesShopifyRequests
{
    /**
     * Execute a Shopify GraphQL query and back off if the API throttle is running low.
     *
     * Shopify's GraphQL API returns extensions.cost.throttleStatus after each query.
     * If the available bucket drops below 500 points we sleep long enough for it to
     * restore before the next request, preventing hard 429 failures on large imports.
     */
    protected function queryWithThrottle(Graphql $client, array $params): mixed
    {
        $response = $client->query($params);

        $status = Arr::get($response->getDecodedBody(), 'extensions.cost.throttleStatus');

        if ($status && isset($status['currentlyAvailable'], $status['restoreRate'])) {
            $available = (float) $status['currentlyAvailable'];
            $restoreRate = (float) $status['restoreRate'];
            $threshold = 500.0;

            if ($available < $threshold && $restoreRate > 0) {
                $sleepSeconds = (int) ceil(($threshold - $available) / $restoreRate);
                $this->throttleSleep(max(1, $sleepSeconds));
            }
        }

        return $response;
    }

    /**
     * Sleep for the given number of seconds. Extracted so tests can override it.
     */
    protected function throttleSleep(int $seconds): void
    {
        sleep($seconds);
    }
}
