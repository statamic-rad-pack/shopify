<?php

namespace Jackabox\Shopify\Blueprints;

use Statamic\Facades\Blueprint as StatamicBlueprint;

class VariantBlueprint extends Blueprint
{
    public function __invoke()
    {
        return StatamicBlueprint::make()->setContents([
            'sections' => [
                'main' => [
                    'fields' => [
                        [
                            'handle' => 'variant_id',
                            'field' => [
                                'type' => 'hidden',
                                'width' => 100
                            ]
                        ], [
                            'handle' => 'name',
                            'field'  => [
                                'type'       => 'text',
                                'width'      => 75,
                                'display'    => 'Name',
                                'validate'   => 'required',
                            ],
                        ],
                    ]
                ]
            ]
        ]);
    }
}
