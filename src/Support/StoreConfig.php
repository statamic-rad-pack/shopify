<?php

namespace StatamicRadPack\Shopify\Support;

use Illuminate\Support\Facades\Cache;
use Shopify\App\ShopifyApp;
use StatamicRadPack\Shopify\Clients\Graphql;

class StoreConfig
{
    public static function isMultiStore(): bool
    {
        return (bool) config('shopify.multi_store.enabled', false);
    }

    public static function getMode(): string
    {
        return config('shopify.multi_store.mode', 'unified');
    }

    public static function isPrimaryStore(string $handle): bool
    {
        return config('shopify.multi_store.primary_store') === $handle;
    }

    public static function isMarketsMode(): bool
    {
        return static::isMultiStore() && static::getMode() === 'markets';
    }

    public static function getMarkets(): array
    {
        return config('shopify.multi_store.markets', []);
    }

    public static function getMarket(string $handle): ?array
    {
        return config('shopify.multi_store.markets.'.$handle);
    }

    public static function findByHandle(string $handle): ?array
    {
        $stores = config('shopify.multi_store.stores', []);

        if (! isset($stores[$handle])) {
            return null;
        }

        return array_merge(['handle' => $handle], $stores[$handle]);
    }

    public static function findBySite(string $siteHandle): ?array
    {
        $stores = config('shopify.multi_store.stores', []);

        foreach ($stores as $handle => $store) {
            if (($store['site'] ?? null) === $siteHandle) {
                return array_merge(['handle' => $handle], $store);
            }
        }

        return null;
    }

    public static function findByDomain(string $domain): ?array
    {
        $stores = config('shopify.multi_store.stores', []);
        $incomingDomain = trim($domain, '/');

        foreach ($stores as $handle => $store) {
            $storeUrl = trim($store['url'] ?? '', '/');

            if ($storeUrl === $incomingDomain) {
                return array_merge(['handle' => $handle], $store);
            }
        }

        return null;
    }

    /**
     * Create (or retrieve from container) a Graphql client for the given store config.
     * Binds the client under 'shopify.graphql.{handle}' so tests can easily mock it.
     *
     * The binding is a factory rather than a fixed instance, so the access token is
     * re-read from the cache on every resolve and long-running processes (queue
     * workers, Octane) pick up a fresh token once the old one expires.
     */
    public static function makeGraphqlClient(array $storeConfig): Graphql
    {
        $handle = $storeConfig['handle'] ?? 'unknown';
        $abstract = 'shopify.graphql.'.$handle;

        if (! app()->bound($abstract)) {
            app()->bind($abstract, fn () => new Graphql(
                $storeConfig['url'] ?? config('shopify.url'),
                static::accessToken($storeConfig) ?? 'none',
                $storeConfig['api_version'] ?? config('shopify.api_version', '2025-04'),
            ));
        }

        return app($abstract);
    }

    /**
     * The legacy admin_token if configured, else a cached OAuth client_credentials token.
     */
    private static function accessToken(array $storeConfig): ?string
    {
        if ($token = ($storeConfig['admin_token'] ?? null)) {
            return $token;
        }

        $cacheKey = 'shopify::admin_token::'.($storeConfig['handle'] ?? 'unknown');

        if ($token = Cache::get($cacheKey)) {
            return $token;
        }

        if ($token = static::exchangeClientCredentials($storeConfig)) {
            Cache::put($cacheKey, $token, 1400);
        }

        return $token;
    }

    private static function exchangeClientCredentials(array $storeConfig): ?string
    {
        if (! ($storeConfig['client_id'] ?? null)) {
            return null;
        }

        $result = (new ShopifyApp(
            clientId: $storeConfig['client_id'],
            clientSecret: $storeConfig['client_secret'] ?? '',
        ))->exchangeUsingClientCredentials(
            shop: Graphql::shop($storeConfig['url'] ?? config('shopify.url')),
        );

        return $result->ok ? $result->accessToken->token : null;
    }
}
