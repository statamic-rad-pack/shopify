<?php

namespace StatamicRadPack\Shopify\Traits;

use Illuminate\Support\Facades\Log;
use Statamic\Support\Arr;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Exceptions\GraphqlException;

trait ThrottlesShopifyRequests
{
    protected int $throttleThreshold = 500;

    protected int $throttleRetries = 3;

    /**
     * Execute a Shopify GraphQL query and back off if the API throttle is running low.
     *
     * Shopify's GraphQL API returns extensions.cost.throttleStatus after each query.
     * If the available bucket drops below the threshold we sleep long enough for it to
     * restore before the next request, preventing hard throttling on large imports.
     * A THROTTLED response is retried; any other failed query (no data returned)
     * throws a GraphqlException rather than looking like an empty result.
     */
    protected function queryWithThrottle(Graphql $client, array $params): mixed
    {
        $attempt = 0;

        do {
            $response = $client->query($params);
            $body = $response->getDecodedBody();
            $throttled = $this->isThrottled($body);

            if ($throttled && ++$attempt < $this->throttleRetries) {
                $this->throttleSleep($this->secondsUntilAvailable($body, (float) Arr::get($body, 'extensions.cost.requestedQueryCost', $this->throttleThreshold)));
            }
        } while ($throttled && $attempt < $this->throttleRetries);

        $this->guardAgainstErrors($response, $params);

        if (($seconds = $this->secondsUntilAvailable($body, $this->throttleThreshold)) > 0) {
            $this->throttleSleep($seconds);
        }

        return $response;
    }

    private function isThrottled(array $body): bool
    {
        return collect(Arr::wrap($body['errors'] ?? []))
            ->contains(fn ($error) => is_array($error) && Arr::get($error, 'extensions.code') === 'THROTTLED');
    }

    /**
     * Seconds to wait until the bucket holds the given number of points (0 if it already does).
     */
    private function secondsUntilAvailable(array $body, float $needed): int
    {
        $status = Arr::get($body, 'extensions.cost.throttleStatus');

        if (! $status || ! isset($status['currentlyAvailable'], $status['restoreRate'])) {
            return 0;
        }

        $available = (float) $status['currentlyAvailable'];
        $restoreRate = (float) $status['restoreRate'];

        if ($available >= $needed || $restoreRate <= 0) {
            return 0;
        }

        return max(1, (int) ceil(($needed - $available) / $restoreRate));
    }

    /**
     * Throw when the query failed outright; log when it only partially failed
     * (e.g. a field the app has no access scope for), as the data is still usable.
     */
    private function guardAgainstErrors(HttpResponse $response, array $params): void
    {
        $body = $response->getDecodedBody();
        $errors = $body['errors'] ?? null;
        $failedRequest = $response->getStatusCode() >= 400;

        if (! $errors && ! $failedRequest) {
            return;
        }

        $message = is_string($errors)
            ? $errors
            : (Arr::get($errors ?? [], '0.message') ?? 'HTTP '.$response->getStatusCode());

        if (empty($body['data'])) {
            throw new GraphqlException($message, Arr::wrap($errors));
        }

        Log::warning('Shopify: GraphQL query returned errors: '.$message, [
            'errors' => $errors,
            'query' => $params['query'] ?? null,
        ]);
    }

    /**
     * Sleep for the given number of seconds. Extracted so tests can override it.
     */
    protected function throttleSleep(int $seconds): void
    {
        sleep($seconds);
    }
}
