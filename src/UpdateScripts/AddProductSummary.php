<?php

namespace StatamicRadPack\Shopify\UpdateScripts;

use Statamic\Facades\Entry;
use Statamic\UpdateScripts\UpdateScript;
use StatamicRadPack\Shopify\Support\ProductSummary;

/**
 * Backfill the price/stock summary (price_min, price_max, in_stock, on_sale) onto
 * existing products from their stored variants, so the product_has_stock scope and
 * the price/stock tags work without a full re-import.
 */
class AddProductSummary extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('8.0.1');
    }

    public function update()
    {
        Entry::query()
            ->where('collection', config('shopify.collection_handle', 'products'))
            ->get()
            ->reject->hasOrigin()
            ->each(fn ($product) => ProductSummary::apply($product)->saveQuietly());

        $this->console()?->info('Shopify product price and stock summaries have been added.');
    }
}
