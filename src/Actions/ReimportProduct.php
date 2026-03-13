<?php

namespace StatamicRadPack\Shopify\Actions;

use Statamic\Actions\Action;
use Statamic\Contracts\Entries\Entry;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Support\StoreConfig;

class ReimportProduct extends Action
{
    public string $icon = 'arrow-down';

    public static function title()
    {
        return __('shopify::messages.reimport_product');
    }

    public function visibleTo($item)
    {
        return $this->context['view'] === 'form'
            && $item instanceof Entry
            && $item->collectionHandle() === config('shopify.collection_handle', 'products');
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function authorize($user, $item)
    {
        return $user->can('access shopify');
    }

    public function run($entries, $values)
    {
        $entries->each(function (Entry $entry) {
            $storeHandle = null;

            if (StoreConfig::isMultiStore() && StoreConfig::getMode() === 'localized') {
                $storeHandle = StoreConfig::findBySite($entry->locale())['handle'] ?? null;
            }

            ImportSingleProductJob::dispatch((int) $entry->get('product_id'), [], $storeHandle);
        });
    }
}
