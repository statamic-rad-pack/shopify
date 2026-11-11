<?php

namespace StatamicRadPack\Shopify\Scopes;

use Statamic\Query\Scopes\Scope;

class ProductHasStock extends Scope
{
    /**
     * Uses the in_stock summary stored on the product at import (see ProductSummary),
     * rather than loading every variant.
     */
    public function apply($query, $values)
    {
        $query->where('in_stock', true);
    }
}
