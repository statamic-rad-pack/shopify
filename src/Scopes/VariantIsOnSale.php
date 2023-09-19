<?php

namespace StatamicRadPack\Shopify\Scopes;

use Statamic\Query\Scopes\Scope;

class VariantIsOnSale extends Scope
{
    public function apply($query, $values)
    {
        $query->whereColumn('compare_at_price', '>', 'price');
    }
}
