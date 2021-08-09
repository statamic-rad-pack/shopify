<?php

namespace Jackabox\Shopify\Tags;

use Jackabox\Shopify\Traits\HasProductVariants;
use Statamic\Tags\Tags;

class ProductVariants extends Tags
{
    use HasProductVariants;

    /**
     * Kept in for backwards compatibility.
     *
     * @deprecated
     *
     * @return string|array
     */
    public function index()
    {
        return $this->generate();
    }

    /**
     * Generate the output of the variants automatically.
     * Saves having to manually call the index/options.
     *
     * @return string|array
     */
    public function generate()
    {
        $variants = $this->fetchProductVariants($this->context->get('slug'));

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
     * Return the collection back so we can use it on the front end.
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function loop()
    {
        dump($this->fetchProductVariants($this->context->get('slug')));
        return $this->fetchProductVariants($this->context->get('slug'));
    }

    /**
     * Return a single variant by the title.
     *
     * @return mixed|null
     */
    public function fromTitle()
    {
        if (!$this->params->get('title')) {
            return null;
        }

        $variants = $this->fetchProductVariants($this->context->get('slug'));

        return $variants
            ->where('title', $this->params->get('title'))
            ->first();
    }

    /**
     * Return a single variant by the index.
     *
     * @return mixed|null
     */
    public function fromIndex()
    {
        if ($this->params->get('index') === null) {
            return null;
        }

        $variants = $this->fetchProductVariants($this->context->get('slug'));

        return $variants
            ->splice($this->params->get('index'), 1)
            ->first();
    }

    /**
     * @return string
     */
    private function startSelect(): string
    {
        return '<select name="ss-product-variant" id="ss-product-variant" class="ss-variant-select ' . $this->params->get('class') . '">';
    }

    /**
     * @param $variants
     * @param null $currency
     * @return string
     */
    private function parseOptions($variants): string
    {
        $html = '';

        foreach ($variants as $variant) {
            $title = $variant['title'];
            $out_of_stock = false;
            $disabled = '';

            if (isset($variant['inventory_policy'])) {
                if ($variant['inventory_policy'] === 'deny' && $variant['inventory_quantity'] === 0) {
                    $out_of_stock = true;
                    $disabled = 'disabled';
                }
            }

            if ($this->params->get('show_price')) {
                $title .= ' - ' . config('shopify.currency') . $variant['price'];
            }

            if ($this->params->get('show_out_of_stock') && $out_of_stock) {
                $title .= ' (' . config('shopify.lang.out_of_stock') . ')';
            }

            $html .= '<option value="' . $variant['storefront_id'] . '" ' . $disabled . '>' . $title . '</option>';
        }

        return $html;
    }

    /**
     * @return string
     */
    private function endSelect(): string
    {
        return '</select>';
    }
}
