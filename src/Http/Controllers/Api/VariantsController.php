<?php

namespace Jackabox\Shopify\Http\Controllers\Api;

use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;

class VariantsController extends CpController
{
    public function fetch($product)
    {
        return Entry::query()
            ->where('collection', 'variants')
            ->get()
            ->filter(function ($item) use ($product) {
                return $item->product_slug == $product;
            })
            ->map(function ($variant) {
                return [
                    'id'              => $variant->id,
                    'product_slug'    => $variant->product_slug,
                    'title'           => $variant->title
                ];
            });
    }
}
