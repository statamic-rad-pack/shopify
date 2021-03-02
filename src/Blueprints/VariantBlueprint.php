<?php

namespace Jackabox\Shopify\Blueprints;

use Statamic\Facades\Blueprint as StatamicBlueprint;

class VariantBlueprint extends Blueprint
{
    public function __invoke()
    {
        $bp = StatamicBlueprint::find('collections/variants/variant');
        ray($bp);

        if (! $bp) {
            // TODO: THROW PROPER ERROR
            return 'No variant data found';
        }

        return $bp;
    }
}
