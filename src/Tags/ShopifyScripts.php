<?php

namespace Jackabox\Shopify\Tags;

use Statamic\Tags\Tags;

class ShopifyScripts extends Tags
{
    protected static $handle = "shopify";

    /**
     * The {{ shopify:scripts }} tag.
     *
     * @return string|array
     */
    public function index()
    {
    }

    public function scripts()
    {
        $html = "<script>
window.shopifyDomain = '" . config('shopify.app_url') . "';
window.shopifyToken = '" . config('shopify.storefront_token') . "';
</script>";

        $html .= '<script src="' . url('/vendor/shopify/js/statamic-shopify-front.js'). '" async></script>';

        return $html;
    }
}
