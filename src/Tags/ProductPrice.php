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

        if ($variants->count() === 0) {
            return;
        }

        $html = '';

        $variants = $variants->pluck('price');

        if ($variants->count() > 1 && $this->params->get('show_from') === true) {
            $html .= 'From ';
        }

        $html .= config('shopify.currency') . $variants->sort()->splice(0, 1)[0];

        return $html;
    }
}
