<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Traits\FetchAllProducts;

/**
 * Page through every product id in Shopify and queue an import for each, so the
 * CP "import all" request doesn't have to paginate the whole catalogue itself.
 */
class ImportAllProductsJob implements ShouldQueue
{
    use Dispatchable;
    use FetchAllProducts;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(public ?string $storeHandle = null, public string $source = Activity::SOURCE_CP)
    {
        if ($queue = config('shopify.queue')) {
            $this->onQueue($queue);
        }
    }

    public function handle(): void
    {
        $client = StoreConfig::clientFor($this->storeHandle);

        foreach ($this->fetchProducts($client) as $productId) {
            $this->callJob($productId, $this->storeHandle, $this->source);
        }
    }
}
