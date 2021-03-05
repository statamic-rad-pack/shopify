<?php

namespace Jackabox\Shopify\Tags;

use Statamic\Facades\Entry;
use Statamic\Tags\Tags;

class ProductVariants extends Tags
{
    /**
     * @return string|array
     */
    public function index()
    {
        if (!$this->params->get('product')) {
            return;
        }

        $variants = Entry::query()
            ->where('collection', 'variants')
            ->where('product_slug', $this->params->get('product'))
            ->get()
            ->map(function ($variant) {
                $values = [];
                $values['id'] = $variant->id();
                $values['slug'] = $variant->slug();

                // Map all variant values to data to ensure we are getting everything.
                foreach ($variant->data() as $key => $value) {
                    $values[$key] = $value;
                }

                return $values;
            });

        if ($variants->count() === 0) {
            return;
        }

        if ($variants->count() > 1) {
            $html = $this->startSelect();
            $html .= $this->parseOptions($variants, $this->params->get('currency'));
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
    public function parseOptions($variants, $currency = null): string
    {
        $html = '';

        foreach ($variants as $variant) {
            $title = $variant['title'];

            if ($currency) {
                $title .= ' - ' . $currency . $variant['price'];
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
