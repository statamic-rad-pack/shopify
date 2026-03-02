<?php

namespace StatamicRadPack\Shopify\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Shopify\Clients\Graphql;

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

    public static function findByHandle(string $handle): ?array
    {
        $stores = config('shopify.multi_store.stores', []);

        if (! isset($stores[$handle])) {
            return null;
        }

        return array_merge(['handle' => $handle], $stores[$handle]);
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
     */
    public static function makeGraphqlClient(array $storeConfig): Graphql
    {
        $handle = $storeConfig['handle'] ?? 'unknown';
        $abstract = 'shopify.graphql.'.$handle;

        if (! app()->bound($abstract)) {
            $url = $storeConfig['url'] ?? config('shopify.url');
            $apiVersion = $storeConfig['api_version'] ?? config('shopify.api_version', '2025-04');

            if ($token = ($storeConfig['admin_token'] ?? null)) {
                $capturedToken = $token;
                app()->bind($abstract, fn () => new Graphql($url, $capturedToken, $apiVersion));
            } else {
                // OAuth client_credentials flow
                $cacheKey = 'shopify::admin_token::'.$handle;

                if (! $token = Cache::get($cacheKey)) {
                    $token = static::exchangeClientCredentials($storeConfig);
                    if ($token) {
                        Cache::put($cacheKey, $token, 1400);
                    }
                }

                $capturedToken = $token ?? 'none';
                app()->bind($abstract, fn () => new Graphql($url, $capturedToken, $apiVersion));
            }
        }

        return app($abstract);
    }

    private static function exchangeClientCredentials(array $storeConfig): ?string
    {
        if (! ($storeConfig['client_id'] ?? null)) {
            return null;
        }

        $response = Http::asForm()->post('https://'.$storeConfig['url'].'/admin/oauth/access_token', [
            'grant_type' => 'client_credentials',
            'client_id' => $storeConfig['client_id'],
            'client_secret' => $storeConfig['client_secret'] ?? '',
        ]);

        return $response->json('access_token');
    }
}
