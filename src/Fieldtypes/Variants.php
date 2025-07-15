<?php

namespace StatamicRadPack\Shopify\Fieldtypes;

use Statamic\Fields\Fieldtype;
use StatamicRadPack\Shopify\Blueprints\VariantBlueprint;

class Variants extends Fieldtype
{
    protected $categories = ['shopify'];

    protected $icon = 'tags';

    public function preload()
    {
        $product = $this->field()->parent();

        if (! $product->slug()) {
            return;
        }

        $variantBlueprint = new VariantBlueprint;
        $variantFields = $variantBlueprint()->fields()->preProcess();

        return [
            'action' => cp_route('shopify.variants.store'),
            'variantIndexRoute' => cp_route('shopify.variants.index', $product->slug()),
            'variantManageRoute' => cp_route('shopify.variants.store'),
            'variantBlueprint' => $variantBlueprint()->toPublishArray(),
            'variantValues' => $variantFields->values(),
            'variantMeta' => $variantFields->meta(),
            'productSlug' => $product->slug(),
        ];
    }
}
