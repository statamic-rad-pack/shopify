<?php

namespace Jackabox\Shopify\Tags;

use Statamic\Tags\Tags;

class ShopifyScripts extends Tags
{
    /**
     * @return string|array
     */
    public function index()
    {
        return '<script src="' . url('/js/vendor/statamic-shopify-front.js') . '"></script>';
    }
}
