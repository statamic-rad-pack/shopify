<?php

namespace StatamicRadPack\Shopify\Fieldtypes;

use Statamic\Contracts\Entries\Entry;
use Statamic\Fields\Fieldtype;
use StatamicRadPack\Shopify\Blueprints\VariantBlueprint;

class Variants extends Fieldtype
{
    protected $categories = ['shopify'];

    protected $icon = 'tags';

    public function preload()
    {
        if (! $product = $this->field()->parent()) {
            return [];
        }

        if (! $product instanceof Entry) {
            return [];
        }

        $variantBlueprint = new VariantBlueprint;
        $variantFields = $variantBlueprint()->fields()->preProcess();

        $slug = $product->slug();

        return [
            'action' => cp_route('shopify.variants.store'),
            'variantIndexRoute' => cp_route('shopify.variants.index', $slug),
            'variantManageRoute' => cp_route('shopify.variants.store'),
            'variantBlueprint' => $variantBlueprint()->toPublishArray(),
            'variantValues' => $variantFields->values(),
            'variantMeta' => $variantFields->meta(),
            'productSlug' => $slug,
        ];
    }
}
