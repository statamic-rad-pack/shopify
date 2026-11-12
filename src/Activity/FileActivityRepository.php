<?php

namespace StatamicRadPack\Shopify\Activity;

use Carbon\CarbonInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;
use Statamic\Support\Str;
use StatamicRadPack\Shopify\Contracts\ActivityRepository;

/**
 * Stores each activity as a JSON file in a folder per day. Suitable for a single
 * server; use the database driver when several servers or workers share the log.
 */
class FileActivityRepository implements ActivityRepository
{
    public function __construct(
        protected Filesystem $files,
        protected string $path,
    ) {}

    public function create(array $attributes): Activity
    {
        $activity = Activity::fromArray(array_merge([
            'id' => Activity::newId(),
            'source' => Activity::SOURCE_WEBHOOK,
            'status' => ActivityStatus::Received,
        ], $attributes));

        $this->write($activity);

        return $activity;
    }

    public function update(string $id, array $attributes): ?Activity
    {
        if (! $activity = $this->find($id)) {
            return null;
        }

        $activity = $activity->fill($attributes);

        $this->write($activity);

        return $activity;
    }

    public function find(string $id): ?Activity
    {
        if (! $this->validId($id) || ! $this->files->exists($file = $this->fileFor($id))) {
            return null;
        }

        return $this->read($file);
    }

    public function paginate(array $filters = [], int $perPage = 25, int $page = 1): LengthAwarePaginator
    {
        $matching = $this->all()->filter(fn (Activity $activity) => $this->matches($activity, $filters));

        $items = $matching->skip(($page - 1) * $perPage)->take($perPage)->values()->collect();

        return new LengthAwarePaginator($items, $matching->count(), $perPage, $page);
    }

    public function prune(CarbonInterface $before): int
    {
        $count = 0;

        foreach ($this->dayFolders() as $folder) {
            if (basename($folder) >= $before->format('Ymd')) {
                continue;
            }

            $count += count($this->files->files($folder));
            $this->files->deleteDirectory($folder);
        }

        // the day the cut-off falls on, file by file
        $cutoffFolder = $this->path.'/'.$before->format('Ymd');

        if ($this->files->isDirectory($cutoffFolder)) {
            foreach ($this->files->files($cutoffFolder) as $file) {
                if ($this->read($file->getPathname())?->createdAt->lt($before)) {
                    $this->files->delete($file->getPathname());
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Every activity, newest first, read lazily.
     */
    protected function all(): LazyCollection
    {
        return LazyCollection::make(function () {
            foreach ($this->dayFolders() as $folder) {
                $files = collect($this->files->files($folder))->map->getPathname()->sortDesc();

                foreach ($files as $file) {
                    if ($activity = $this->read($file)) {
                        yield $activity;
                    }
                }
            }
        });
    }

    protected function matches(Activity $activity, array $filters): bool
    {
        $data = $activity->toArray();

        foreach (['status', 'source', 'topic', 'store', 'resource_type'] as $key) {
            if (filled($filters[$key] ?? null) && $data[$key] !== $filters[$key]) {
                return false;
            }
        }

        if (filled($search = $filters['search'] ?? null)) {
            return Str::contains((string) $activity->resourceTitle, $search, true)
                || $activity->resourceId === (string) $search
                || Str::contains((string) $activity->message, $search, true);
        }

        return true;
    }

    /**
     * Day folders, newest first.
     */
    protected function dayFolders(): array
    {
        if (! $this->files->isDirectory($this->path)) {
            return [];
        }

        return collect($this->files->directories($this->path))
            ->filter(fn ($folder) => preg_match('/^\d{8}$/', basename($folder)))
            ->sortDesc()
            ->values()
            ->all();
    }

    protected function write(Activity $activity): void
    {
        $file = $this->fileFor($activity->id);

        $this->files->ensureDirectoryExists(dirname($file));
        $this->files->put($file, json_encode($activity->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }

    protected function read(string $file): ?Activity
    {
        $data = json_decode((string) @file_get_contents($file), true);

        return is_array($data) ? Activity::fromArray($data) : null;
    }

    protected function fileFor(string $id): string
    {
        return $this->path.'/'.substr($id, 0, 8).'/'.$id.'.json';
    }

    /**
     * Ids come from Activity::newId(); anything else could escape the log folder.
     */
    protected function validId(string $id): bool
    {
        return (bool) preg_match('/^\d{8}-\d{6}-\d{3}-[a-z0-9]{8}$/', $id);
    }
}
