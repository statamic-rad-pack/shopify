<?php

namespace StatamicRadPack\Shopify\UpdateScripts;

use Statamic\Facades\Collection;
use Statamic\Support\Arr;
use Statamic\UpdateScripts\UpdateScript;

class AddMultiStoreDataToVariantBlueprint extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return config('shopify.multi_store.enabled', false)
            && config('shopify.multi_store.mode', 'unified') === 'unified'
            && $this->isUpdatingTo('7.0.0');
    }

    public function update()
    {
        if (! $variantCollection = Collection::find('variants')) {
            return;
        }

        if (! $blueprint = $variantCollection->entryBlueprint()) {
            return;
        }

        $currentContents = $blueprint->contents();
        $currentFields = Arr::get($currentContents, 'tabs.main.sections.0.fields', []);

        $exists = collect($currentFields)->contains(fn ($field) => ($field['handle'] ?? null) === 'multi_store_data');

        if ($exists) {
            return;
        }

        $currentFields[] = [
            'handle' => 'multi_store_data',
            'field' => [
                'display' => 'Multi-Store Pricing',
                'type' => 'array',
                'icon' => 'array',
                'listable' => 'hidden',
                'instructions_position' => 'above',
                'visibility' => 'read_only',
            ],
        ];

        Arr::set($currentContents, 'tabs.main.sections.0.fields', $currentFields);
        $blueprint->setContents($currentContents);
        $blueprint->save();
    }
}
