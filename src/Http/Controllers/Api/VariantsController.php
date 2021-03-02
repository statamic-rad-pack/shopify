<?php

namespace Jackabox\Shopify\Http\Controllers\Api;

use Illuminate\Http\Request;
use Jackabox\Shopify\Blueprints\VariantBlueprint;
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
                $values = [];
                $values['id'] = $variant->id();

                // Map all variant values to data to ensure we are getting everything.
                foreach ($variant->data() as $key => $value) {
                    $values[$key] = $value;
                }

                return $values;
            });
    }

    public function store() {}

    public function update(Request $request)
    {
        if (! $request->id) {
            // TODO: Throw error
            return;
        }

        // Match the values to the blueprint, validate.
        $blueprint = new VariantBlueprint();
        $fields = $blueprint()->fields()->addValues($request->all());
        $fields->validate();
        $values = $fields->process()->values()->toArray();

        // Find and update the entry
        $variant = Entry::find($request->id);
        $variant->data($values);
        $variant->save();
    }
}
