<?php

namespace StatamicRadPack\Shopify\Clients;

use Shopify\App\ShopifyApp;

/**
 * Thin wrapper around shopify/shopify-app-php's adminGraphQLRequest() that keeps
 * the query(['query' => ..., 'variables' => ...]) / getDecodedBody() interface the
 * rest of the addon (and the tests) were written against for shopify/shopify-api.
 */
class Graphql
{
    protected string $apiVersion;

    protected ShopifyApp $app;

    public function __construct(
        protected string $domain,
        protected string $token,
        ?string $apiVersion = null,
        ?ShopifyApp $app = null,
    ) {
        $this->apiVersion = $apiVersion ?? config('shopify.api_version', '2025-04');
        $this->app = $app ?? app(ShopifyApp::class);
    }

    public function query(array $params): HttpResponse
    {
        $result = $this->app->adminGraphQLRequest(
            query: $params['query'] ?? '',
            shop: static::shop($this->domain),
            accessToken: $this->token,
            apiVersion: $this->apiVersion,
            variables: $params['variables'] ?? null,
            maxRetries: 0,
        );

        return new HttpResponse(
            status: $result->response->status,
            body: $result->response->body,
        );
    }

    /**
     * Reduce a configured shop URL (my-store.myshopify.com, with or without scheme
     * or trailing slash) to the bare shop handle shopify-app-php expects.
     */
    public static function shop(string $domain): string
    {
        $host = parse_url($domain, PHP_URL_HOST) ?? $domain;

        return str_replace('.myshopify.com', '', trim($host, '/'));
    }
}
