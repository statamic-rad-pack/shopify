<?php

namespace StatamicRadPack\Shopify\Tags;

use Statamic\Support\Str;
use Statamic\Tags\Concerns\QueriesConditions;
use Statamic\Tags\Concerns\QueriesOrderBys;
use Statamic\Tags\Tags;

class Shopify extends Tags
{
    use QueriesConditions, QueriesOrderBys;

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
        return '<script src="'.url('/vendor/statamic-shopify/js/statamic-shopify-front.js').'"></script>';
    }

    /**
     * @return string|array
     */
    public function tokens()
    {
        return "<script>
window.shopifyUrl = '".(config('shopify.storefront_url') ?? config('shopify.url'))."';
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
            $html = '<select name="ss-product-variant" id="ss-product-variant" class="ss-variant-select '.$this->params->get('class').'">';
            $html .= $this->variantSelectOptions($variants);
            $html .= '</select>';
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
    private function variants()
    {
        return $this->fetchProductVariants($this->context->get('slug'));
    }

    /*
     * @deprecated
     */
    private function variantLoop()
    {
        return 'No longer supported, use {{ shopify:variants }} instead';
    }

    /*
     * @deprecated
     */
    private function variantFromTitle()
    {
        return 'No longer supported, use {{ shopify:variants title:is="title" }} instead';
    }

    /*
     * @deprecated
     */
    private function variantFromIndex()
    {
        return 'No longer supported, use {{ {shopify:variants}[index] }} instead';
    }

    /**
     * Turn a variant into a select option
     *
     * @param  array  $variants
     */
    private function variantSelectOptions($variants): string
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

    /**
     * Get product variants for a given product slug
     *
     * @param  string  $productSlug
     */
    protected function fetchProductVariants($productSlug)
    {
        $query = Entry::query()
            ->where('collection', 'variants')
            ->where('product_slug', $productSlug);

        $this->queryConditions($query);
        $this->queryOrderBys($query);

        $entries = $query->get();

        if (! $entries->count()) {
            return null;
        }

        return $entries->map(function ($variant) {
            $values = [];
            $values['id'] = $variant->id();
            $values['slug'] = $variant->slug();

            // Map all variant values to data to ensure we are getting everything.
            foreach ($variant->data() as $key => $value) {
                $values[$key] = $value;
            }

            return $values;
        });
    }

    /**
     * Check if all variants are in stock
     *
     * @param  array  $variants
     */
    protected function isInStock($variants): bool
    {
        $stock = 0;
        $deny = false;

        foreach ($variants as $variant) {
            $stock += $variant['inventory_quantity'];

            if (isset($variant['inventory_policy']) && isset($variant['inventory_management'])) {
                $deny = $variant['inventory_policy'] === 'deny' && $variant['inventory_management'] === 'shopify';
            }
        }

        if ($stock === 0 and $deny) {
            return false;
        }

        return true;
    }
}
