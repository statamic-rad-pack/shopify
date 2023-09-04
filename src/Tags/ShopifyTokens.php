<?php

namespace StatamicRadPack\Shopify\Tags;

use Statamic\Tags\Tags;

class ShopifyTokens extends Tags
{
    /**
     * @return string|array
     */
    public function index()
    {
        return "<script>
window.shopifyUrl = '".config('shopify.url')."';
window.shopifyToken = '".config('shopify.storefront_token')."';
</script>";
    }
}
