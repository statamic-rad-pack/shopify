<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Support\Arr;

class ShopifyUnifiedMultiStoreDisable extends Command
{
    use RunsInPlease;

    protected $signature = 'shopify:unified-multi-store:disable';

    protected $description = 'Removes the multi_store_data field from the variants blueprint';

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

        $currentFields = array_values(
            array_filter($currentFields, fn ($field) => ($field['handle'] ?? null) !== 'multi_store_data')
        );

        Arr::set($currentContents, 'tabs.main.sections.0.fields', $currentFields);
        $blueprint->setContents($currentContents);
        $blueprint->save();

        $this->info('multi_store_data field has been removed from the variants blueprint.');
    }
}
