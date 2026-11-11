<?php

namespace StatamicRadPack\Shopify\Listeners;

use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Blink;

/**
 * Clear the per-request variant memo used by the shopify tags whenever a variant changes.
 */
class FlushVariantCache
{
    public function handle(EntrySaved|EntryDeleted $event): void
    {
        if ($event->entry->collectionHandle() === 'variants') {
            Blink::store('shopify-variants')->flush();
        }
    }
}
