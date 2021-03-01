<?php

namespace Jackabox\Shopify\Fieldtypes;

use Jackabox\Shopify\Blueprints\VariantBlueprint;
use Statamic\Fields\Fieldtype;

class Variants extends Fieldtype
{
    protected $categories = ['shopify'];
    protected $icon = 'tags';

    public function preload()
    {
        $product = $this->field()->parent();
        $variantBlueprint = new VariantBlueprint();
        $variantFields = $variantBlueprint()->fields()->addValues([])->preProcess();

        return [
            'action'             => 'action',
            'variantIndexRoute'  => cp_route('shopify.variants.index', $product->slug()),
            'variantManageRoute' => 'manage',
            'variantBlueprint'   => $variantBlueprint()->toPublishArray(),
            'variantValues'      => $variantFields->values(),
            'variantMeta'        => $variantFields->meta(),
            'productSlug'        => $product->slug(),
        ];
    }

    public function process($data)
    {
        return $data;
    }

    public function preProcess($data)
    {
        return $data;
    }
}
