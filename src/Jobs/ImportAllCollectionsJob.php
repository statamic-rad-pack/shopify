<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Traits\FetchCollections;

/**
 * Page through every manual and smart collection id in Shopify and queue an import
 * for each, so the CP "import collections" request doesn't paginate them itself.
 */
class ImportAllCollectionsJob implements ShouldQueue
{
    use Dispatchable;
    use FetchCollections;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(public ?string $storeHandle = null)
    {
        if ($queue = config('shopify.queue')) {
            $this->onQueue($queue);
        }
    }

    public function handle(): void
    {
        $client = StoreConfig::clientFor($this->storeHandle);

        collect($this->getManualCollections($client))
            ->merge($this->getSmartCollections($client))
            ->each(fn ($collectionId) => ImportCollectionJob::dispatch($collectionId, $this->storeHandle));
    }
}
