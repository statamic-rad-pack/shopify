<?php

namespace Jackabox\Shopify\Tags;

use Statamic\Facades\Entry;
use Statamic\Tags\Tags;

class ShopifyScripts extends Tags
{
    protected static $handle = "shopify";

    /**
     * @return string|array
     */
    public function index()
    {
    }

    /**
     * Return the shopify config tokens
     *
     * @return string
     */
    public function tokens(): string
    {
        return "<script>
window.shopifyUrl = '" . config('shopify.url') . "';
window.shopifyToken = '" . config('shopify.storefront_token') . "';
</script>";
    }

    /**
     * Dumps out the demo JS
     *
     * @return string
     */
    public function scripts(): string
    {
        return '<script src="' . url('/vendor/shopify/js/statamic-shopify-front.js'). '" async></script>';
    }
}
