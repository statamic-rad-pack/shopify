<?php

namespace StatamicRadPack\Shopify\Tags;

use Statamic\Tags\Tags;
use StatamicRadPack\Shopify\Traits\HasProductVariants;

class ProductPrice extends Tags
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

        $html = '';

        // Out of Stock
        if (! $this->isInStock($variants)) {
            return __('shopify.out_of_stock');
        }

        // Lowest Price
        $pricePluck = $variants->pluck('price');

        $price = $pricePluck->sort()->splice(0, 1)[0];

        if ($pricePluck->count() > 1 && $this->params->get('show_from') === true) {
            return __('shopify.display_price_from', ['currency' => config('shopify.currency'), 'price' => $price]);
        }

        return __('shopify.display_price', ['currency' => config('shopify.currency'), 'price' => $price]);
    }
}
