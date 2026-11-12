<?php

namespace StatamicRadPack\Shopify\Support;

use Statamic\Contracts\Auth\User;
use Statamic\Support\Arr;

/**
 * Where a Statamic user's Shopify customer ID lives, and which stores they're synced to.
 *
 * With several Shopify stores (unified or localized multi-store) each store has its
 * own customers, so a user has an ID per store in `shopify_ids` (keyed by store
 * handle). Single-store sites, and markets mode (one Shopify store), use `shopify_id`.
 */
class Customers
{
    /**
     * Whether customers have a separate ID in each store.
     */
    public static function perStore(): bool
    {
        return StoreConfig::hasSeparateStores();
    }

    /**
     * The store a store param refers to for customers: null unless customers are
     * per store and it's a configured store.
     */
    public static function store(?string $store): ?string
    {
        return static::perStore() && $store && StoreConfig::findByHandle($store) ? $store : null;
    }

    /**
     * The user's Shopify customer ID in the store (or the only store).
     */
    public static function id(User $user, ?string $store = null): ?string
    {
        $store = static::store($store);

        $id = $store ? Arr::get($user->get('shopify_ids', []), $store) : $user->get('shopify_id');

        return $id ? (string) $id : null;
    }

    /**
     * Link the user to a Shopify customer in the store (or the only store), without saving.
     */
    public static function setId(User $user, ?string $store, int|string $id): User
    {
        if (! $store = static::store($store)) {
            return $user->set('shopify_id', (int) $id);
        }

        return $user->set('shopify_ids', array_merge($user->get('shopify_ids', []), [$store => (int) $id]));
    }

    /**
     * The stores a Statamic user is created or updated in: the stores they're already
     * linked to, or `multi_store.customer_store` (else the primary store) for someone
     * not linked to any. [null] means the only store.
     *
     * @return array<int, string|null>
     */
    public static function syncStores(User $user): array
    {
        if (! static::perStore()) {
            return [null];
        }

        $linked = collect($user->get('shopify_ids', []))
            ->filter()
            ->keys()
            ->filter(fn ($store) => StoreConfig::findByHandle($store))
            ->values()
            ->all();

        if ($linked) {
            return $linked;
        }

        $default = config('shopify.multi_store.customer_store') ?: config('shopify.multi_store.primary_store');

        return $default && StoreConfig::findByHandle($default) ? [$default] : [];
    }
}
