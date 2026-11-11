<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Actions;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\Controller;

class VariantsController extends Controller
{
    public function fetch(Request $request, string $product)
    {
        $query = Entry::query()->where('collection', 'variants');

        // Shopify products have at most three options
        foreach (range(1, 3) as $index) {
            if ($option = $request->get('option'.$index)) {
                $query->where('option'.$index, $option);
            }
        }

        return $query
            ->where('product_slug', $product)
            ->get()
            ->map(function ($variant) {
                $values['title'] = $variant->title;
                $values['id'] = $variant->variant_id;
                $values['price'] = $variant->price;
                $values['inventory_quantity'] = $variant->inventory_quantity;

                return $values;
            });
    }
}
