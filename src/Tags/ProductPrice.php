<?php

namespace Jackabox\Shopify\Tags;

use Jackabox\Shopify\Traits\HasProductVariants;
use Statamic\Tags\Tags;

class ProductPrice extends Tags
{
    use HasProductVariants;

    /**
     * @return string|array
     */
    public function index()
    {
        if (!$this->params->get('product')) {
            return;
        }

        $variants = $this->fetchProductVariants($this->params->get('product'));

        if (!$variants) {
            return null;
        }

        $html = '';

        // Out of Stock
        if (!$this->isInStock($variants)) {
            return config('shopify.lang.out_of_stock', 'Out of Stock');
        }

        // Lowest Price
        $pricePluck = $variants->pluck('price');

        if ($pricePluck->count() > 1 && $this->params->get('show_from') === true) {
            $html .= config('shopify.lang.from', 'From') . ' ';
        }

        $html .= config('shopify.currency') . $pricePluck->sort()->splice(0, 1)[0];

        return $html;
    }
}
