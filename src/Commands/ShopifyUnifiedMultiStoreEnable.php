<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Support\Arr;

class ShopifyUnifiedMultiStoreEnable extends Command
{
    use RunsInPlease;

    protected $signature = 'shopify:unified-multi-store:enable';

    protected $description = 'Adds the multi_store_data field to the variants blueprint for unified multi-store mode';

    public function handle()
    {
        if (! $variantCollection = Collection::find('variants')) {
            $this->error('Variants collection not found.');

            return;
        }

        if (! $blueprint = $variantCollection->entryBlueprint()) {
            $this->error('Variants blueprint not found.');

            return;
        }

        $currentContents = $blueprint->contents();
        $currentFields = Arr::get($currentContents, 'tabs.main.sections.0.fields', []);

        $exists = collect($currentFields)->contains(fn ($field) => ($field['handle'] ?? null) === 'multi_store_data');

        if ($exists) {
            $this->info('multi_store_data field already exists in the variants blueprint.');

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

        $this->info('multi_store_data field has been added to the variants blueprint.');
    }
}
