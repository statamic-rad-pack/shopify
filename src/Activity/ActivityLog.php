<?php

namespace StatamicRadPack\Shopify\Activity;

use Illuminate\Support\Facades\Log;
use StatamicRadPack\Shopify\Contracts\ActivityRepository;
use StatamicRadPack\Shopify\Jobs\ImportCollectionJob;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use Throwable;

/**
 * Records activity without ever getting in the way: when the log is disabled or its
 * storage fails, webhooks and imports carry on and the failure is only logged.
 */
class ActivityLog
{
    public static function enabled(): bool
    {
        return (bool) config('shopify.activity.enabled', true);
    }

    /**
     * Record a new activity, returning its id (or null if it couldn't be recorded).
     */
    public static function record(array $attributes): ?string
    {
        return static::attempt(fn (ActivityRepository $activity) => $activity->create($attributes)->id);
    }

    public static function update(?string $id, array $attributes): void
    {
        if ($id) {
            static::attempt(fn (ActivityRepository $activity) => $activity->update($id, $attributes));
        }
    }

    /**
     * Queue a product import, recording it as its own activity unless an existing
     * activity (e.g. the webhook delivery that triggered it) is passed in.
     */
    public static function queueProductImport(
        int $productId,
        ?string $storeHandle = null,
        string $source = Activity::SOURCE_CLI,
        ?string $activityId = null,
        array $orderData = [],
        array $attributes = [],
    ): ?string {
        $activityId = static::queued($activityId, array_merge([
            'source' => $source,
            'store' => $storeHandle,
            'resource_type' => 'product',
            'resource_id' => (string) $productId,
        ], $attributes));

        ImportSingleProductJob::dispatch($productId, $orderData, $storeHandle, $activityId);

        return $activityId;
    }

    /**
     * Queue a collection import, recording it the same way as queueProductImport().
     */
    public static function queueCollectionImport(
        int $collectionId,
        ?string $storeHandle = null,
        string $source = Activity::SOURCE_CLI,
        ?string $activityId = null,
        array $attributes = [],
    ): ?string {
        $activityId = static::queued($activityId, array_merge([
            'source' => $source,
            'store' => $storeHandle,
            'resource_type' => 'collection',
            'resource_id' => (string) $collectionId,
        ], $attributes));

        ImportCollectionJob::dispatch($collectionId, $storeHandle, $activityId);

        return $activityId;
    }

    protected static function queued(?string $activityId, array $attributes): ?string
    {
        if ($activityId) {
            static::update($activityId, ['status' => ActivityStatus::Queued->value]);

            return $activityId;
        }

        return static::record(array_merge($attributes, ['status' => ActivityStatus::Queued->value]));
    }

    protected static function attempt(callable $callback): mixed
    {
        if (! static::enabled()) {
            return null;
        }

        try {
            return $callback(app(ActivityRepository::class));
        } catch (Throwable $e) {
            Log::warning('Shopify: could not record activity: '.$e->getMessage());

            return null;
        }
    }
}
