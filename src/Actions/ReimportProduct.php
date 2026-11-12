<?php

namespace StatamicRadPack\Shopify\Actions;

use Statamic\Actions\Action;
use Statamic\Contracts\Entries\Entry;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Support\StoreConfig;

/**
 * Reimport one or more products from Shopify, from the entry form or the listing
 * (including bulk selections). Each import is queued and shows in the activity log.
 */
class ReimportProduct extends Action
{
    public string $icon = 'arrow-down';

    public static function title()
    {
        return __('shopify::messages.reimport_product');
    }

    public function visibleTo($item)
    {
        return $this->isProduct($item);
    }

    public function visibleToBulk($items)
    {
        return $items->isNotEmpty() && $items->every(fn ($item) => $this->isProduct($item));
    }

    public function authorize($user, $item)
    {
        return $user->can('access shopify');
    }

    public function buttonText()
    {
        return __('shopify::messages.reimport_button');
    }

    public function confirmationText()
    {
        return __('shopify::messages.reimport_confirmation');
    }

    public function run($entries, $values)
    {
        $queued = $entries
            ->filter(fn (Entry $entry) => $entry->get('product_id'))
            // selecting several localizations of a product only imports it once per store
            ->unique(fn (Entry $entry) => $entry->get('product_id').'|'.$this->storeHandle($entry))
            ->each(fn (Entry $entry) => ActivityLog::queueProductImport(
                (int) $entry->get('product_id'),
                $this->storeHandle($entry),
                Activity::SOURCE_CP,
                attributes: ['resource_title' => $entry->get('title')],
            ))
            ->count();

        return trans_choice('shopify::messages.reimport_queued', $queued, ['count' => $queued]);
    }

    protected function isProduct($item): bool
    {
        return $item instanceof Entry
            && $item->collectionHandle() === config('shopify.collection_handle', 'products')
            && filled($item->get('product_id'));
    }

    protected function storeHandle(Entry $entry): ?string
    {
        return StoreConfig::isLocalizedMode()
            ? (StoreConfig::findBySite($entry->locale())['handle'] ?? null)
            : null;
    }
}
