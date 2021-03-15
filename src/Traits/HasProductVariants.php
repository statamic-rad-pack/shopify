<?php

namespace Jackabox\Shopify\Traits;

use Statamic\Facades\Entry;

trait HasProductVariants
{
    protected function fetchProductVariants($product)
    {
        $entries = Entry::query()
            ->where('collection', 'variants')
            ->where('product_slug', $product)
            ->get();

        if (!$entries->count()) {
            return null;
        }

        return $entries->map(function ($variant) {
            $values = [];
            $values['id'] = $variant->id();
            $values['slug'] = $variant->slug();

            // Map all variant values to data to ensure we are getting everything.
            foreach ($variant->data() as $key => $value) {
                $values[$key] = $value;
            }

            return $values;
        });
    }

    protected function isInStock($variants): bool
    {
        $stock = 0;
        $deny = false;

        foreach ($variants as $variant) {
            $stock += $variant['inventory_quantity'];
            $deny = $variant['inventory_policy'] === 'deny';
        }

        if ($stock === 0 and $deny) {
            return false;
        }

        return true;
    }
}
