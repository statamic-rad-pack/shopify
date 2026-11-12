<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Facades\Entry;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Activity\ActivityStatus;
use StatamicRadPack\Shopify\Support\ProductSummary;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Traits\ThrottlesShopifyRequests;
use Throwable;

/**
 * Updates a variant's stock after an inventory_levels/update webhook.
 *
 * The webhook only has the inventory item and one location's count, so this
 * fetches the variant's total stock and updates just that variant and its
 * product's summary, rather than re-importing the whole product. Multi-store
 * sites keep stock per store or market, so their product is re-imported instead.
 */
class UpdateInventoryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use ThrottlesShopifyRequests;

    public $tries = 3;

    public $backoff = [10, 60];

    public function __construct(
        public int $inventoryItemId,
        public ?string $storeHandle = null,
        public ?string $activityId = null,
    ) {
        if ($queue = config('shopify.queue')) {
            $this->onQueue($queue);
        }
    }

    public function handle(): void
    {
        $query = <<<'GQL'
            query ($id: ID!) {
              inventoryItem(id: $id) {
                tracked
                variant {
                  id
                  title
                  inventoryQuantity
                  inventoryPolicy
                  product {
                    id
                    title
                  }
                }
              }
            }
            GQL;

        $response = $this->queryWithThrottle(StoreConfig::clientFor($this->storeHandle), [
            'query' => $query,
            'variables' => ['id' => "gid://shopify/InventoryItem/{$this->inventoryItemId}"],
        ]);

        $item = Arr::get($response->getDecodedBody(), 'data.inventoryItem');

        if (! $variant = Arr::get($item, 'variant')) {
            ActivityLog::update($this->activityId, [
                'status' => ActivityStatus::Skipped->value,
                'message' => 'No variant uses this inventory item',
            ]);

            return;
        }

        $productId = (int) Str::afterLast($variant['product']['id'], '/');

        $activity = [
            'resource_type' => 'product',
            'resource_id' => (string) $productId,
            'resource_title' => $variant['product']['title'] ?? null,
        ];

        // from here the activity is about the product, so a failed one can be retried
        ActivityLog::update($this->activityId, $activity);

        if (StoreConfig::isMultiStore()) {
            ActivityLog::queueProductImport($productId, $this->storeHandle, Activity::SOURCE_WEBHOOK, $this->activityId, attributes: $activity);

            return;
        }

        $entries = Entry::query()
            ->where('collection', 'variants')
            ->where('slug', Str::afterLast($variant['id'], '/'))
            ->get()
            ->reject(fn ($entry) => $entry->hasOrigin());

        if ($entries->isEmpty()) {
            ActivityLog::update($this->activityId, $activity + [
                'status' => ActivityStatus::Skipped->value,
                'message' => 'Variant not imported',
            ]);

            return;
        }

        foreach ($entries as $entry) {
            // same fields and rules as ImportSingleProductJob
            $entry->merge([
                'inventory_quantity' => $variant['inventoryQuantity'] ?? null,
                'inventory_policy' => ($item['tracked'] ?? false) ? ($variant['inventoryPolicy'] ?? null) : 'CONTINUE',
            ])->save();

            ProductSummary::syncForVariant($entry);
        }

        ActivityLog::update($this->activityId, $activity + [
            'status' => ActivityStatus::Updated->value,
            'message' => sprintf('Stock for %s is now %s', $variant['title'] ?? 'the variant', $variant['inventoryQuantity'] ?? 'unknown'),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        ActivityLog::update($this->activityId, [
            'status' => ActivityStatus::Failed->value,
            'message' => $exception->getMessage(),
        ]);
    }
}
