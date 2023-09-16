<?php

namespace StatamicRadPack\Shopify\Tags;

use Statamic\Support\Str;
use Statamic\Tags\Tags;
use StatamicRadPack\Shopify\Traits\HasProductVariants;

class Shopify extends Tags
{
    use HasProductVariants;

    /**
     * @return string|array
     */
    public function inStock()
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

    /**
     * @return string|array
     */
    public function productPrice()
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
            return config('shopify.lang.out_of_stock', 'Out of Stock');
        }

        // Lowest Price
        $pricePluck = $variants->pluck('price');

        if ($pricePluck->count() > 1 && $this->params->get('show_from') === true) {
            $html .= config('shopify.lang.from', 'From').' ';
        }

        $html .= config('shopify.currency').$pricePluck->sort()->splice(0, 1)[0];

        return $html;
    }

    /**
     * @return string|array
     */
    public function scripts()
    {
        return '<script src="'.url('/js/vendor/statamic-shopify-front.js').'"></script>';
    }

    /**
     * @return string|array
     */
    public function tokens()
    {
        return "<script>
window.shopifyUrl = '".config('shopify.url')."';
window.shopifyToken = '".config('shopify.storefront_token')."';
</script>";
    }

    /**
     * handle any shopify:variants:x tags
     */
    public function wildcard($tag)
    {
        if (str_contains($tag, ':') && Str::before($tag, ':') == 'variants') {
            $method = 'variant'.Str::studly(Str::after($tag, ':'));

            if (method_exists($this, $method)) {
                return $this->{$method}();
            }
        }
    }

    /**
     * Generate the output of the variants automatically.
     * Saves having to manually call the index/options.
     *
     * @return string|array
     */
    private function variantGenerate()
    {
        $variants = $this->fetchProductVariants($this->context->get('slug'));

        if (! $variants) {
            return;
        }

        if ($variants->count() > 1) {
            $html = $this->startSelect();
            $html .= $this->variantParseOptions($variants);
            $html .= $this->endSelect();
        } else {
            $html = '<input type="hidden" name="ss-product-variant" id="ss-product-variant" value="'.$variants[0]['storefront_id'].'">';
        }

        return $html;
    }

    /**
     * Return the collection back so we can use it on the front end.
     *
     * @return \Illuminate\Support\Collection|null
     */
    private function variantLoop()
    {
        return $this->fetchProductVariants($this->context->get('slug'));
    }

    /**
     * Return a single variant by the title.
     *
     * @return mixed|null
     */
    private function variantFromTitle()
    {
        if (! $this->params->get('title')) {
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
    private function variantFromIndex()
    {
        if ($this->params->get('index') === null) {
            return null;
        }

        $variants = $this->fetchProductVariants($this->context->get('slug'));

        return $variants
            ->splice($this->params->get('index'), 1)
            ->first();
    }

    private function startSelect(): string
    {
        return '<select name="ss-product-variant" id="ss-product-variant" class="ss-variant-select '.$this->params->get('class').'">';
    }

    /**
     * @param  null  $currency
     */
    private function variantParseOptions($variants): string
    {
        $html = '';

        foreach ($variants as $variant) {
            $title = $variant['title'];
            $out_of_stock = false;

            if (isset($variant['inventory_policy']) && isset($variant['inventory_management'])) {
                if (
                    $variant['inventory_policy'] === 'deny' &&
                    $variant['inventory_management'] === 'shopify' &&
                    $variant['inventory_quantity'] <= 0
                ) {
                    $out_of_stock = true;
                }
            }

            if ($this->params->get('show_price')) {
                $title .= ' - '.config('shopify.currency').$variant['price'];
            }

            if ($this->params->get('show_out_of_stock') && $out_of_stock) {
                $title .= ' ('.config('shopify.lang.out_of_stock').')';
            }

            $html .= '<option value="'.$variant['storefront_id'].'" data-in-stock="'.$out_of_stock.'"'.($out_of_stock ? ' disabled' : '').'>'.$title.'</option>';
        }

        return $html;
    }

    private function endSelect(): string
    {
        return '</select>';
    }
}
