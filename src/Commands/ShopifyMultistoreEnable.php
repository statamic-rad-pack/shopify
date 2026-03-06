<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Support\Arr;

class ShopifyMultistoreEnable extends Command
{
    use RunsInPlease;

    protected $signature = 'shopify:multistore:enable {--mode= : The multi-store mode to enable (unified or markets). Defaults to the value in config.}';

    protected $description = 'Adds the appropriate multi-store field to the variants blueprint';

    public function handle()
    {
        $mode = $this->option('mode') ?? config('shopify.multi_store.mode', 'unified');

        if (! in_array($mode, ['unified', 'markets'])) {
            $this->error("Unknown mode \"{$mode}\". Valid values are: unified, markets.");

            return;
        }

        if (! $variantCollection = Collection::find('variants')) {
            $this->error('Variants collection not found.');

            return;
        }

        if (! $blueprint = $variantCollection->entryBlueprint()) {
            $this->error('Variants blueprint not found.');

            return;
        }

        [$fieldHandle, $displayName] = $mode === 'markets'
            ? ['market_data', 'Market Pricing']
            : ['multi_store_data', 'Multi-Store Pricing'];

        $currentContents = $blueprint->contents();
        $currentFields = Arr::get($currentContents, 'tabs.main.sections.0.fields', []);

        $exists = collect($currentFields)->contains(fn ($field) => ($field['handle'] ?? null) === $fieldHandle);

        if ($exists) {
            $this->info("{$fieldHandle} field already exists in the variants blueprint.");

            return;
        }

        $currentFields[] = [
            'handle' => $fieldHandle,
            'field' => [
                'display' => $displayName,
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

        $this->info("{$fieldHandle} field has been added to the variants blueprint.");
    }
}
