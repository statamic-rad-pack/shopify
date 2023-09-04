<?php

namespace StatamicRadPack\Shopify\Scopes;

use Statamic\Query\Scopes\Scope;

class VariantByProduct extends Scope
{
    public function apply($query, $values)
    {
        if ($values['product']) {
            $query->where('product_slug', $values['product']);
        }
    }
}
