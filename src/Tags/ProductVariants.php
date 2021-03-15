<?php

namespace Jackabox\Shopify\Tags;

use Jackabox\Shopify\Traits\HasProductVariants;
use Statamic\Tags\Tags;

class ProductVariants extends Tags
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
            return;
        }

        if ($variants->count() > 1) {
            $html = $this->startSelect();
            $html .= $this->parseOptions($variants);
            $html .= $this->endSelect();
        } else {
            $html = '<input type="hidden" name="ss-product-variant" id="ss-product-variant" value="' . $variants[0]['storefront_id'] . '">';
        }

        return $html;
    }

    /**
     * @return string
     */
    public function startSelect(): string
    {
        return '<select name="ss-product-variant" id="ss-product-variant" class="ss-variant-select ' . $this->params->get('class') . '">';
    }

    /**
     * @param $variants
     * @param null $currency
     * @return string
     */
    public function parseOptions($variants): string
    {
        $html = '';

        foreach ($variants as $variant) {
            $title = $variant['title'];
            $out_of_stock = false;

            if (isset($variant['inventory_policy'])) {
                if ($variant['inventory_policy'] === 'deny' && $variant['inventory_quantity'] === 0) {
                    $out_of_stock = true;
                }
            }

            if ($this->params->get('show_price')) {
                $title .= ' - ' . config('shopify.currency') . $variant['price'];
            }

            if ($this->params->get('show_out_of_stock') && $out_of_stock) {
                $title .= ' (' . config('shopify.lang.out_of_stock') . ')';
            }

            $html .= '<option value="' . $variant['storefront_id'] . '" data-in-stock="' . $out_of_stock . '">' . $title . '</option>';
        }

        return $html;
    }

    /**
     * @return string
     */
    public function endSelect(): string
    {
        return '</select>';
    }
}
