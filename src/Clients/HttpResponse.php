<?php

namespace StatamicRadPack\Shopify\Clients;

/**
 * Drop-in replacement for Shopify\Clients\HttpResponse (shopify/shopify-api).
 *
 * Only the surface the addon actually consumes is implemented: construction with
 * named status/body/headers args (as the tests do) and getDecodedBody().
 */
class HttpResponse
{
    public function __construct(
        public int $status = 200,
        public array|string|null $body = null,
        public array $headers = [],
    ) {}

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): array|string|null
    {
        return $this->body;
    }

    public function getDecodedBody(): array
    {
        if (is_array($this->body)) {
            return $this->body;
        }

        return json_decode($this->body ?: '{}', true) ?? [];
    }
}
