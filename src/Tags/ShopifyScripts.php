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
        return '<script src="' . public_path('/vendor/shopify/js/shopify.js'). '"></script>';
    }
}
