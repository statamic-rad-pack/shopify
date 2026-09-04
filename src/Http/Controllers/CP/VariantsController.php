<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Shopify\Blueprints\VariantBlueprint;

class VariantsController extends CpController
{
    public function fetch(Request $request, $product)
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }

        $site = Site::selected()->handle();

        return Entry::query()
            ->where('collection', 'variants')
            ->where('product_slug', $product)
            ->where('site', $site)
            ->get()
            ->map(function ($variant) {
                return $variant->values()->merge([
                    'id' => $variant->id(),
                    'slug' => $variant->slug(),
                ]);
            });
    }

    public function store() {}

    public function update(Request $request)
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }

        if (! $request->id) {
            // TODO: Throw error
            return;
        }

        // Find the entry, and make sure it's actually a variant before we touch it.
        $variant = Entry::find($request->id);

        if (! $variant || $variant->collectionHandle() !== 'variants') {
            abort(404);
        }

        // Match the values to the blueprint, validate.
        $blueprint = new VariantBlueprint;
        $fields = $blueprint()->fields()->addValues($request->all());
        $fields->validate();
        $values = $fields->process()->values()->toArray();

        $variant->data($values);
        $variant->save();
    }
}
