<?php

namespace StatamicRadPack\Shopify\Listeners;

use Statamic\Events\AssetContainerBlueprintFound;

/**
 * Shows which Shopify image an imported asset came from, read only, in the
 * asset editor. Added at runtime rather than written to the blueprint, so it
 * needs no update script and can't be removed by accident.
 */
class AddShopifyFieldsToAssetBlueprint
{
    public function handle(AssetContainerBlueprintFound $event): void
    {
        if ($event->container?->handle() !== config('shopify.asset.container')) {
            return;
        }

        // the blueprint is shared by every asset in the container, so the field
        // is always added and hidden on assets that weren't imported from Shopify
        $event->blueprint->ensureField('shopify_media_id', [
            'type' => 'text',
            'display' => __('Shopify ID'),
            'instructions' => __('The Shopify image this asset was imported from.'),
            'visibility' => 'read_only',
            'listable' => 'hidden',
            'if' => ['shopify_media_id' => 'not empty'],
        ]);
    }
}
