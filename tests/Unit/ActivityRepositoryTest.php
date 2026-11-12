<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Carbon\CarbonImmutable;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityStatus;
use StatamicRadPack\Shopify\Activity\DatabaseActivityRepository;
use StatamicRadPack\Shopify\Activity\FileActivityRepository;
use StatamicRadPack\Shopify\Contracts\ActivityRepository;
use StatamicRadPack\Shopify\Tests\TestCase;

class ActivityRepositoryTest extends TestCase
{
    public static function drivers(): array
    {
        return [
            'file' => ['file'],
            'database' => ['database'],
        ];
    }

    private function repository(string $driver): ActivityRepository
    {
        if ($driver === 'database') {
            $migration = require __DIR__.'/../../database/migrations/2026_11_11_000000_create_shopify_activity_table.php';
            $migration->up();

            return new DatabaseActivityRepository($this->app['db'], null, 'shopify_activity');
        }

        return new FileActivityRepository(new Filesystem, __DIR__.'/../__fixtures__/dev-null/activity-repository');
    }

    #[Test]
    #[DataProvider('drivers')]
    public function creates_finds_and_updates_activity(string $driver)
    {
        $repository = $this->repository($driver);

        $activity = $repository->create([
            'source' => Activity::SOURCE_WEBHOOK,
            'status' => ActivityStatus::Received->value,
            'topic' => 'products/update',
            'resource_type' => 'product',
            'resource_id' => '123',
        ]);

        $this->assertSame('products/update', $repository->find($activity->id)->topic);

        $repository->update($activity->id, [
            'status' => ActivityStatus::Updated->value,
            'resource_title' => 'Shirt',
            'changes' => ['added' => 2],
        ]);

        $found = $repository->find($activity->id);
        $this->assertSame(ActivityStatus::Updated, $found->status);
        $this->assertSame('Shirt', $found->resourceTitle);
        $this->assertSame(['added' => 2], $found->changes);
        $this->assertSame('123', $found->resourceId);

        $this->assertNull($repository->find('20260101-000000-000-missing0'));
        $this->assertNull($repository->update('20260101-000000-000-missing0', ['status' => 'failed']));
    }

    #[Test]
    #[DataProvider('drivers')]
    public function paginates_newest_first_with_filters(string $driver)
    {
        $repository = $this->repository($driver);

        foreach (range(1, 5) as $i) {
            CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-01-01 10:00:00')->addMinutes($i));

            $repository->create([
                'source' => $i % 2 ? Activity::SOURCE_WEBHOOK : Activity::SOURCE_CLI,
                'status' => $i === 5 ? ActivityStatus::Failed->value : ActivityStatus::Updated->value,
                'resource_type' => 'product',
                'resource_id' => (string) $i,
                'resource_title' => "Product {$i}",
            ]);
        }

        CarbonImmutable::setTestNow();

        $page = $repository->paginate([], 2, 1);
        $this->assertSame(5, $page->total());
        $this->assertSame(['5', '4'], collect($page->items())->pluck('resourceId')->all());
        $this->assertSame(['3', '2'], collect($repository->paginate([], 2, 2)->items())->pluck('resourceId')->all());

        $this->assertSame(3, $repository->paginate(['source' => Activity::SOURCE_WEBHOOK])->total());
        $this->assertSame(['5'], collect($repository->paginate(['status' => 'failed'])->items())->pluck('resourceId')->all());
        $this->assertSame(['3'], collect($repository->paginate(['search' => 'product 3'])->items())->pluck('resourceId')->all());
        $this->assertSame(['4'], collect($repository->paginate(['search' => '4'])->items())->pluck('resourceId')->all());
    }

    #[Test]
    #[DataProvider('drivers')]
    public function prunes_activity_older_than_a_date(string $driver)
    {
        $repository = $this->repository($driver);

        CarbonImmutable::setTestNow('2026-01-01 12:00:00');
        $old = $repository->create(['source' => Activity::SOURCE_CLI, 'status' => 'updated']);

        CarbonImmutable::setTestNow('2026-02-01 08:00:00');
        $sameDayOld = $repository->create(['source' => Activity::SOURCE_CLI, 'status' => 'updated']);

        CarbonImmutable::setTestNow('2026-02-01 12:00:00');
        $recent = $repository->create(['source' => Activity::SOURCE_CLI, 'status' => 'updated']);

        CarbonImmutable::setTestNow();

        $this->assertSame(2, $repository->prune(CarbonImmutable::parse('2026-02-01 10:00:00')));

        $this->assertNull($repository->find($old->id));
        $this->assertNull($repository->find($sameDayOld->id));
        $this->assertNotNull($repository->find($recent->id));
    }

    #[Test]
    public function the_file_driver_ignores_ids_that_could_escape_its_folder()
    {
        $repository = $this->repository('file');

        $this->assertNull($repository->find('../../../../composer'));
        $this->assertNull($repository->update('../secrets', ['status' => 'failed']));
    }
}
