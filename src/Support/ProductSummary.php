<?php

namespace StatamicRadPack\Shopify\Support;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;

/**
 * Price and stock values derived from a product's variants, stored on the product
 * entry so listings, sorting and the product_has_stock scope don't have to query
 * every variant.
 */
class ProductSummary
{
    public const FIELDS = ['price_min', 'price_max', 'in_stock', 'on_sale'];

    /**
     * @param  iterable<array|EntryContract>  $variants
     */
    public static function fromVariants(iterable $variants): array
    {
        $variants = static::normalise($variants);

        // keep the raw price values (e.g. "19.99") so formatting matches the variants
        $priced = $variants
            ->filter(fn ($variant) => is_numeric($variant['price'] ?? null))
            ->sortBy(fn ($variant) => (float) $variant['price']);

        return [
            'price_min' => $priced->first()['price'] ?? null,
            'price_max' => $priced->last()['price'] ?? null,
            'in_stock' => static::inStock($variants),
            'on_sale' => $variants->contains(
                fn ($variant) => is_numeric($variant['compare_at_price'] ?? null)
                    && is_numeric($variant['price'] ?? null)
                    && (float) $variant['compare_at_price'] > (float) $variant['price']
            ),
        ];
    }

    /**
     * A product is in stock if any of its variants can be purchased: either it has
     * stock, or it doesn't deny overselling (CONTINUE / untracked).
     *
     * @param  iterable<array|EntryContract>  $variants
     */
    public static function inStock(iterable $variants): bool
    {
        return static::normalise($variants)->contains(
            fn ($variant) => strtolower($variant['inventory_policy'] ?? '') !== 'deny'
                || ($variant['inventory_quantity'] ?? 0) > 0
        );
    }

    /**
     * Recalculate the summary for a product entry from its stored variants (without saving).
     */
    public static function apply(EntryContract $product): EntryContract
    {
        $variants = Entry::query()
            ->where('collection', 'variants')
            ->where('product_slug', $product->slug())
            ->where('site', $product->locale())
            ->get();

        return $product->merge(static::fromVariants($variants));
    }

    /**
     * Recalculate and save the summary for the product a variant belongs to.
     */
    public static function syncForVariant(EntryContract $variant): void
    {
        // the summary lives on the origin product, built from the origin variants
        $variant = $variant->root();

        $product = Entry::query()
            ->where('collection', config('shopify.collection_handle', 'products'))
            ->where('slug', $variant->get('product_slug'))
            ->where('site', $variant->locale())
            ->first();

        if ($product && ! $product->hasOrigin()) {
            static::apply($product)->save();
        }
    }

    private static function normalise(iterable $variants): Collection
    {
        return collect($variants)->map(
            fn ($variant) => $variant instanceof EntryContract ? $variant->values()->all() : (array) $variant
        );
    }
}
