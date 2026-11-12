<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Contracts\ActivityRepository;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Tests\TestCase;

class ActivityLogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['collections', 'tags', 'type', 'vendor'] as $taxonomy) {
            Facades\Taxonomy::make($taxonomy)->save();
        }
    }

    /**
     * @return array<int, Activity> newest first
     */
    private function activity(array $filters = []): array
    {
        return app(ActivityRepository::class)->paginate($filters, 100)->items();
    }

    private function productJson(): string
    {
        return json_encode(['data' => ['product' => [
            'id' => 'gid://shopify/Product/10',
            'handle' => 'shirt',
            'title' => 'Shirt',
            'descriptionHtml' => '',
            'tags' => [],
            'vendor' => '',
            'productType' => '',
            'options' => [],
            'collections' => ['edges' => []],
            'metafields' => ['edges' => []],
            'media' => ['edges' => []],
            'resourcePublications' => ['edges' => []],
            'variants' => ['edges' => [
                ['node' => ['id' => 'gid://shopify/ProductVariant/100', 'title' => 'Small', 'price' => '10.00', 'compareAtPrice' => null, 'sku' => 's', 'inventoryQuantity' => 1, 'inventoryPolicy' => 'DENY', 'inventoryItem' => ['tracked' => true], 'selectedOptions' => []]],
                ['node' => ['id' => 'gid://shopify/ProductVariant/101', 'title' => 'Large', 'price' => '12.00', 'compareAtPrice' => null, 'sku' => 'l', 'inventoryQuantity' => 0, 'inventoryPolicy' => 'DENY', 'inventoryItem' => ['tracked' => true], 'selectedOptions' => []]],
            ]],
        ]]]);
    }

    private function mockShopify(?string $body = null, int $status = 200): void
    {
        $this->mock(Graphql::class, function (MockInterface $mock) use ($body, $status) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: $status, body: $body ?? $this->productJson()));
        });
    }

    #[Test]
    public function records_a_product_webhook_through_to_the_import_result()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);
        $this->mockShopify();

        $this->postJson('/!/shopify/webhook/product/update', ['id' => 10, 'title' => 'Shirt'], [
            'X-Shopify-Topic' => 'products/update',
            'X-Shopify-Webhook-Id' => 'delivery-1',
        ])->assertOk();

        [$activity] = $this->activity();
        $entry = Facades\Entry::query()->where('collection', 'products')->where('product_id', '10')->first();

        $this->assertSame('webhook', $activity->source);
        $this->assertSame('products/update', $activity->topic);
        $this->assertSame('delivery-1', $activity->deliveryId);
        $this->assertSame('updated', $activity->status->value);
        $this->assertSame('product', $activity->resourceType);
        $this->assertSame('10', $activity->resourceId);
        $this->assertSame('Shirt', $activity->resourceTitle);
        $this->assertSame($entry->id(), $activity->entryId);
        $this->assertSame(['added' => 2, 'updated' => 0, 'removed' => 0], $activity->changes);
        $this->assertNotNull($entry->get('shopify_synced_at'));
    }

    #[Test]
    public function records_variant_changes_on_a_later_import()
    {
        $this->mockShopify();
        ImportSingleProductJob::dispatch(10);

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $json = json_decode($this->productJson(), true);
            array_pop($json['data']['product']['variants']['edges']);
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: json_encode($json)));
        });

        ActivityLog::queueProductImport(10, source: Activity::SOURCE_CP);

        $this->assertSame(['added' => 0, 'updated' => 1, 'removed' => 1], $this->activity()[0]->changes);
        $this->assertSame('cp', $this->activity()[0]->source);
    }

    #[Test]
    public function records_rejected_and_duplicate_webhooks()
    {
        config()->set('shopify.webhook_secret', 'secret');

        $this->postJson('/!/shopify/webhook/product/update', ['id' => 10, 'title' => 'Not trusted'], ['X-Shopify-Hmac-Sha256' => 'wrong'])
            ->assertForbidden();

        $rejected = $this->activity(['status' => 'rejected'])[0];
        $this->assertSame('products/update', $rejected->topic);
        $this->assertNull($rejected->resourceTitle, 'An unverified payload should not be recorded');

        config()->set('shopify.ignore_webhook_integrity_check', true);
        Bus::fake();

        $headers = ['X-Shopify-Topic' => 'products/update', 'X-Shopify-Webhook-Id' => 'same'];
        $this->postJson('/!/shopify/webhook/product/update', ['id' => 10], $headers)->assertOk();
        $this->postJson('/!/shopify/webhook/product/update', ['id' => 10], $headers)->assertOk();

        $this->assertCount(1, $this->activity(['status' => 'duplicate']));
    }

    #[Test]
    public function records_each_product_an_order_updates_under_the_order()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);
        Bus::fake();

        $this->postJson('/!/shopify/webhook/order', [
            'name' => '#1001',
            'created_at' => '2026-01-01T00:00:00Z',
            'line_items' => [
                ['product_id' => 10, 'variant_id' => 100, 'quantity' => 1, 'title' => 'Shirt'],
                ['product_id' => 20, 'variant_id' => 200, 'quantity' => 1, 'title' => 'Hat'],
            ],
        ], ['X-Shopify-Topic' => 'orders/create'])->assertOk();

        $order = collect($this->activity())->firstWhere('resourceType', 'order');
        $this->assertSame('#1001', $order->resourceTitle);
        $this->assertSame('updated', $order->status->value);
        $this->assertStringContainsString('2 product', $order->message);

        $children = collect($this->activity())->where('parentId', $order->id);
        $this->assertSame(['Hat', 'Shirt'], $children->pluck('resourceTitle')->sort()->values()->all());
        $this->assertSame(['queued'], $children->pluck('status.value')->unique()->values()->all());

        Bus::assertDispatched(ImportSingleProductJob::class, fn ($job) => $job->activityId !== null && $job->activityId !== $order->id);
    }

    #[Test]
    public function records_a_product_delete()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);

        Facades\Entry::make()->collection('products')->slug('shirt')->data(['product_id' => 10, 'title' => 'Shirt'])->save();
        Facades\Entry::make()->collection('variants')->slug('100')->data(['product_slug' => 'shirt'])->save();

        $this->postJson('/!/shopify/webhook/product/delete', ['id' => 10])->assertOk();

        $activity = $this->activity()[0];
        $this->assertSame('deleted', $activity->status->value);
        $this->assertSame('Shirt', $activity->resourceTitle);
        $this->assertSame(['removed' => 1], $activity->changes);
    }

    #[Test]
    public function records_a_failed_import_with_the_error()
    {
        $this->mockShopify('{"errors":"[API] Invalid API key or access token"}', 401);

        try {
            ActivityLog::queueProductImport(10, source: Activity::SOURCE_CP);
        } catch (\Throwable) {
            // the sync queue rethrows after marking the job failed
        }

        $activity = $this->activity()[0];
        $this->assertSame('failed', $activity->status->value);
        $this->assertStringContainsString('Invalid API key', $activity->message);
    }

    #[Test]
    public function imports_and_webhooks_work_when_the_log_is_disabled_or_broken()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);
        Bus::fake();

        config()->set('shopify.activity.enabled', false);
        $this->postJson('/!/shopify/webhook/product/update', ['id' => 10])->assertOk();
        $this->assertCount(0, $this->activity());

        config()->set('shopify.activity.enabled', true);
        $broken = Mockery::mock(ActivityRepository::class);
        $broken->shouldReceive('create', 'update')->andThrow(new \RuntimeException('Disk full'));
        $this->app->instance(ActivityRepository::class, $broken);

        $this->postJson('/!/shopify/webhook/product/update', ['id' => 11])->assertOk();

        Bus::assertDispatchedTimes(ImportSingleProductJob::class, 2);
    }

    #[Test]
    public function the_cp_lists_activity_and_retries_failed_imports()
    {
        $this->actingAs(tap(Facades\User::make()->email('admin@example.com')->makeSuper())->save());

        $failed = app(ActivityRepository::class)->create([
            'source' => Activity::SOURCE_WEBHOOK,
            'status' => 'failed',
            'resource_type' => 'product',
            'resource_id' => '10',
            'resource_title' => 'Shirt',
        ]);
        $updated = app(ActivityRepository::class)->create(['source' => Activity::SOURCE_CLI, 'status' => 'updated', 'resource_type' => 'product', 'resource_id' => '20']);

        $response = $this->getJson(cp_route('shopify.activity.data', ['status' => 'failed']))->assertOk();
        $this->assertSame([$failed->id], array_column($response->json('data'), 'id'));
        $this->assertNotNull($response->json('data.0.retry_url'));

        Bus::fake();

        $this->postJson(cp_route('shopify.activity.retry', $updated->id))->assertStatus(422);

        $newId = $this->postJson(cp_route('shopify.activity.retry', $failed->id))->assertOk()->json('activity_id');

        Bus::assertDispatched(ImportSingleProductJob::class, fn ($job) => $job->productId === 10 && $job->activityId === $newId);
        $this->assertSame($failed->id, app(ActivityRepository::class)->find($newId)->parentId);
    }

    #[Test]
    public function the_cp_activity_endpoints_need_the_access_shopify_permission()
    {
        $this->actingAs(tap(Facades\User::make()->email('nobody@example.com'))->save());

        $this->getJson(cp_route('shopify.activity.data'))->assertForbidden();
        $this->postJson(cp_route('shopify.activity.retry', '20260101-000000-000-abcdefgh'))->assertForbidden();
    }

    #[Test]
    public function the_prune_command_removes_old_activity()
    {
        $repository = app(ActivityRepository::class);

        CarbonImmutable::setTestNow(now()->subDays(40));
        $old = $repository->create(['source' => Activity::SOURCE_CLI, 'status' => 'updated']);
        CarbonImmutable::setTestNow();
        $recent = $repository->create(['source' => Activity::SOURCE_CLI, 'status' => 'updated']);

        $this->artisan('shopify:activity:prune')->assertSuccessful();

        $this->assertNull($repository->find($old->id));
        $this->assertNotNull($repository->find($recent->id));
    }

    #[Test]
    public function records_a_missing_access_scope_as_a_failure_not_a_missing_product()
    {
        $this->mockShopify('{"data":{"product":null},"errors":[{"message":"Access denied for address field. Required access: `read_locations` access scope."}]}');

        try {
            ActivityLog::queueProductImport(10, source: Activity::SOURCE_CLI);
        } catch (\Throwable) {
            // the sync queue rethrows after marking the job failed
        }

        $activity = $this->activity()[0];
        $this->assertSame('failed', $activity->status->value);
        $this->assertStringContainsString('read_locations', $activity->message);
    }

    #[Test]
    public function records_a_product_that_no_longer_exists_as_skipped()
    {
        $this->mockShopify('{"data":{"product":null}}');

        ActivityLog::queueProductImport(10, source: Activity::SOURCE_CLI);

        $this->assertSame('skipped', $this->activity()[0]->status->value);
        $this->assertSame('Product not found in Shopify', $this->activity()[0]->message);
    }
}
