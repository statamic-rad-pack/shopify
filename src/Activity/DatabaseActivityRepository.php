<?php

namespace StatamicRadPack\Shopify\Activity;

use Carbon\CarbonInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use StatamicRadPack\Shopify\Contracts\ActivityRepository;

/**
 * Stores activity in a database table, so every server and queue worker shares one log.
 */
class DatabaseActivityRepository implements ActivityRepository
{
    public function __construct(
        protected ConnectionResolverInterface $db,
        protected ?string $connection,
        protected string $table,
    ) {}

    public function create(array $attributes): Activity
    {
        $activity = Activity::fromArray(array_merge([
            'id' => Activity::newId(),
            'source' => Activity::SOURCE_WEBHOOK,
            'status' => ActivityStatus::Received,
        ], $attributes));

        $this->query()->insert($this->toRow($activity));

        return $activity;
    }

    public function update(string $id, array $attributes): ?Activity
    {
        if (! $activity = $this->find($id)) {
            return null;
        }

        $activity = $activity->fill($attributes);

        $this->query()->where('id', $id)->update($this->toRow($activity));

        return $activity;
    }

    public function find(string $id): ?Activity
    {
        $row = $this->query()->where('id', $id)->first();

        return $row ? Activity::fromArray((array) $row) : null;
    }

    public function paginate(array $filters = [], int $perPage = 25, int $page = 1): LengthAwarePaginator
    {
        $query = $this->query();

        foreach (['status', 'source', 'topic', 'store', 'resource_type'] as $column) {
            if (filled($filters[$column] ?? null)) {
                $query->where($column, $filters[$column]);
            }
        }

        if (filled($search = $filters['search'] ?? null)) {
            $query->where(fn (Builder $query) => $query
                ->where('resource_title', 'like', "%{$search}%")
                ->orWhere('resource_id', $search)
                ->orWhere('message', 'like', "%{$search}%"));
        }

        $total = (clone $query)->count();

        $items = $query->orderByDesc('id')
            ->forPage($page, $perPage)
            ->get()
            ->map(fn ($row) => Activity::fromArray((array) $row));

        return new LengthAwarePaginator($items, $total, $perPage, $page);
    }

    public function prune(CarbonInterface $before): int
    {
        return $this->query()->where('created_at', '<', $before->toDateTimeString())->delete();
    }

    protected function query(): Builder
    {
        return $this->db->connection($this->connection)->table($this->table);
    }

    protected function toRow(Activity $activity): array
    {
        $row = $activity->toArray();

        $row['changes'] = $row['changes'] ? json_encode($row['changes']) : null;
        $row['created_at'] = $activity->createdAt->toDateTimeString();
        $row['updated_at'] = $activity->updatedAt->toDateTimeString();

        return $row;
    }
}
