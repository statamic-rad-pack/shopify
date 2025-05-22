<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Actions;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;

class VariantsController extends CpController
{
    public function fetch(Request $request, string $product)
    {
        return Entry::query()
            ->where('collection', 'variants')
            ->when($option = $request->get('option1'), fn ($query) => $query->where('option1', $option))
            ->when($option = $request->get('option2'), fn ($query) => $query->where('option2', $option))
            ->when($option = $request->get('option3'), fn ($query) => $query->where('option3', $option))
            ->when($option = $request->get('option4'), fn ($query) => $query->where('option4', $option))
            ->when($option = $request->get('option5'), fn ($query) => $query->where('option5', $option))
            ->when($option = $request->get('option6'), fn ($query) => $query->where('option6', $option))
            ->when($option = $request->get('option7'), fn ($query) => $query->where('option7', $option))
            ->where('product_slug', $product)
            ->get()
            ->map(function ($variant) {
                $values['title'] = $variant->title;
                $values['id'] = $variant->variant_id;
                $values['price'] = $variant->price;
                $values['inventory_quantity'] = $variant->inventory_quantity;

                return $values;
            }) ?? [];
    }
}
