<?php

namespace StatamicRadPack\Shopify\UpdateScripts;

use Statamic\Facades\Collection;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\UpdateScripts\UpdateScript;

/**
 * Add the new read-only pricing, inventory and last imported fields to existing
 * product blueprints, in a "Details" tab, so they show in the control panel and
 * can be used as listing columns.
 *
 * Fields a blueprint already has are left where they are, and no other fields are
 * moved: new installs get the full tab layout from resources/blueprints/product.yaml.
 */
class AddSyncFieldsToProductBlueprint extends UpdateScript
{
    public const TAB = 'shopify';

    /**
     * The fields this update introduces.
     */
    public const FIELDS = ['in_stock', 'on_sale', 'price_min', 'price_max', 'shopify_synced_at'];

    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('8.0.1');
    }

    public function update()
    {
        if (! $collection = Collection::find(config('shopify.collection_handle', 'products'))) {
            return;
        }

        $addonTab = Arr::get(YAML::file(__DIR__.'/../../resources/blueprints/product.yaml')->parse(), 'tabs.'.self::TAB);

        $collection->entryBlueprints()->each(function ($blueprint) use ($addonTab) {
            // the addon's sections, keeping only the new fields this blueprint doesn't have yet
            $sections = collect($addonTab['sections'])
                ->map(fn ($section) => array_merge($section, [
                    'fields' => collect($section['fields'])
                        ->filter(fn ($field) => in_array($field['handle'], self::FIELDS) && ! $blueprint->hasField($field['handle']))
                        ->values()
                        ->all(),
                ]))
                ->filter(fn ($section) => $section['fields'])
                ->values()
                ->all();

            if (! $sections) {
                return;
            }

            $contents = $blueprint->contents();

            Arr::set($contents, 'tabs.'.self::TAB.'.display', Arr::get($contents, 'tabs.'.self::TAB.'.display', $addonTab['display']));
            Arr::set($contents, 'tabs.'.self::TAB.'.sections', array_merge(Arr::get($contents, 'tabs.'.self::TAB.'.sections', []), $sections));

            $blueprint->setContents($contents)->save();
        });

        $this->console()?->info('Shopify pricing, inventory and last imported fields have been added to your product blueprint.');
    }
}
