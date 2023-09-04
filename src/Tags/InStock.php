<?php

namespace StatamicRadPack\Shopify\Tags;

use Statamic\Tags\Tags;
use StatamicRadPack\Shopify\Traits\HasProductVariants;

class InStock extends Tags
{
    use HasProductVariants;

    /**
     * @return string|array
     */
    public function index()
    {
        if (! $this->context->get('slug')) {
            return null;
        }

        $variants = $this->fetchProductVariants($this->context->get('slug'));

        if (! $variants) {
            return null;
        }

        return $this->isInStock($variants);
    }
}
