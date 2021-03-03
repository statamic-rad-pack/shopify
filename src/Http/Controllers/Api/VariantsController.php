<?php

namespace Jackabox\Shopify\Http\Controllers\Api;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;

class VariantsController extends CpController
{
    public function fetch(Request $request, $product)
    {
        if (! $request->get('option1')) {
            return response()->json('No options set');
        }

        if (! $product) {
            return response()->json('No product');
        }

        return Entry::query()
            ->where('collection', 'variants')
            ->where('option1', $request->get('option1'))
            ->where('option2', $request->get('option2'))
            ->where('option3', $request->get('option3'))
            ->get()
            ->map(function ($variant) {
                $values['title'] = $variant->title;
                $values['storefront_id'] = $variant->storefront_id;
                $values['price'] = $variant->price;
                $values['inventory_quantity'] = $variant->inventory_quantity;
                return $values;
            })
            ->first();
    }
}
