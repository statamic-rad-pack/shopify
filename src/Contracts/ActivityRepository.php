<?php

namespace StatamicRadPack\Shopify\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use StatamicRadPack\Shopify\Activity\Activity;

interface ActivityRepository
{
    /**
     * Store a new activity. Attributes use snake_case keys (see Activity::toArray()).
     */
    public function create(array $attributes): Activity;

    /**
     * Merge attributes into an existing activity, returning null if it doesn't exist.
     */
    public function update(string $id, array $attributes): ?Activity;

    public function find(string $id): ?Activity;

    /**
     * Newest first. Supported filters: status, source, topic, store, resource_type, search.
     *
     * @return LengthAwarePaginator<Activity>
     */
    public function paginate(array $filters = [], int $perPage = 25, int $page = 1): LengthAwarePaginator;

    /**
     * Delete activity created before the given time, returning how many were removed.
     */
    public function prune(CarbonInterface $before): int;
}
