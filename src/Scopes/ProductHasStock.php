<?php

namespace StatamicRadPack\Shopify\Scopes;

use Statamic\Facades\Entry;
use Statamic\Query\Scopes\Scope;

class ProductHasStock extends Scope
{
    public function apply($query, $values)
    {
        $slugs = Entry::query()
            ->where('collection', 'variants')
            ->where(function ($query) {
                $query->where('inventory_quantity', '>', 0)
                    ->orWhere('inventory_policy', '!=', 'DENY')
                    ->orWhereNull('inventory_policy');
            })
            ->get(['product_slug'])
            ->map->get('product_slug')
            ->unique()
            ->values()
            ->all();

        $query->whereIn('slug', $slugs);
    }
}
