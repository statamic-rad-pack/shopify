<?php

namespace StatamicRadPack\Shopify\Blueprints;

use Statamic\Facades\Blueprint;

class VariantBlueprint extends Blueprint
{
    public function __invoke()
    {
        $bp = Blueprint::find('collections/variants/variant');

        if (! $bp) {
            // TODO: THROW PROPER ERROR
            return 'No variant data found';
        }

        return $bp;
    }
}
