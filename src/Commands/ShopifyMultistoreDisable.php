<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Support\Arr;

class ShopifyMultistoreDisable extends Command
{
    use RunsInPlease;

    protected $signature = 'shopify:multistore:disable {--mode= : The multi-store mode to disable (unified or markets). Defaults to the value in config.}';

    protected $description = 'Removes the appropriate multi-store field from the variants blueprint';

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

        $fieldHandle = $mode === 'markets' ? 'market_data' : 'multi_store_data';

        $currentContents = $blueprint->contents();
        $currentFields = Arr::get($currentContents, 'tabs.main.sections.0.fields', []);

        $currentFields = array_values(
            array_filter($currentFields, fn ($field) => ($field['handle'] ?? null) !== $fieldHandle)
        );

        Arr::set($currentContents, 'tabs.main.sections.0.fields', $currentFields);
        $blueprint->setContents($currentContents);
        $blueprint->save();

        $this->info("{$fieldHandle} field has been removed from the variants blueprint.");
    }
}
