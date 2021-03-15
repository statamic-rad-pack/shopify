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

            if ($this->params->get('show_price')) {
                $title .= ' - ' . config('shopify.currency') . $variant['price'];
            }

            $html .= '<option value="' . $variant['storefront_id'] . '">' . $title . '</option>';
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
