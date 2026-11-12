<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Contracts\ActivityRepository;
use StatamicRadPack\Shopify\Enums\WebhookTopic;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Tests\TestCase;

class InventoryAndRefundWebhooksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('shopify.ignore_webhook_integrity_check', true);

        foreach (['collections', 'tags', 'type', 'vendor'] as $taxonomy) {
            Facades\Taxonomy::make($taxonomy)->save();
        }
    }

    private function latestActivity()
    {
        return app(ActivityRepository::class)->paginate([], 1)->items()[0];
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

    private function mockShopify(string $body): void
    {
        $this->mock(Graphql::class, function (MockInterface $mock) use ($body) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: $body));
        });
    }

    private function inventoryItemJson(int $quantity, bool $tracked = true): string
    {
        return json_encode(['data' => ['inventoryItem' => [
            'tracked' => $tracked,
            'variant' => [
                'id' => 'gid://shopify/ProductVariant/100',
                'title' => 'Small',
                'inventoryQuantity' => $quantity,
                'inventoryPolicy' => 'DENY',
                'product' => ['id' => 'gid://shopify/Product/10', 'title' => 'Shirt'],
            ],
        ]]]);
    }

    private function makeImportedProduct(int $quantity = 0): void
    {
        Facades\Entry::make()->collection('products')->slug('shirt')->data(['title' => 'Shirt', 'product_id' => '10', 'in_stock' => false])->save();
        Facades\Entry::make()->collection('variants')->slug('100')->data(['title' => 'Small', 'product_slug' => 'shirt', 'price' => 10, 'inventory_quantity' => $quantity, 'inventory_policy' => 'DENY'])->save();
    }

    #[Test]
    public function a_refund_removes_its_items_from_purchase_history_on_the_refund_date()
    {
        Bus::fake();

        $this->postJson('/!/shopify/webhook/refund/create', [
            'id' => 1,
            'order_id' => 99,
            'created_at' => '2026-03-05T10:00:00Z',
            'refund_line_items' => [
                ['quantity' => 2, 'line_item_id' => 5, 'line_item' => ['id' => 5, 'product_id' => 10, 'variant_id' => 100, 'sku' => 's', 'title' => 'Shirt']],
                ['quantity' => 1, 'line_item_id' => 6, 'line_item' => ['id' => 6, 'product_id' => null, 'variant_id' => null, 'title' => 'Custom item']],
            ],
        ], ['X-Shopify-Topic' => 'refunds/create'])->assertOk();

        Bus::assertDispatchedTimes(ImportSingleProductJob::class, 1);
        Bus::assertDispatched(ImportSingleProductJob::class, fn ($job) => $job->productId === 10
            && $job->orderData['quantity'] === ['100' => -2]
            && $job->orderData['date']->format('Y-m-d') === '2026-03-05');

        $refund = app(ActivityRepository::class)->paginate(['resource_type' => 'refund'], 1)->items()[0];
        $this->assertSame('Refund for order 99', $refund->resourceTitle);
        $this->assertSame('Removed 1 refunded product(s) from purchase history', $refund->message);
    }

    #[Test]
    public function a_refund_without_products_is_skipped()
    {
        Bus::fake();

        $this->postJson('/!/shopify/webhook/refund/create', ['id' => 1, 'order_id' => 99, 'created_at' => '2026-03-05T10:00:00Z', 'refund_line_items' => []], ['X-Shopify-Topic' => 'refunds/create'])
            ->assertOk();

        Bus::assertNotDispatched(ImportSingleProductJob::class);
        $this->assertSame('skipped', $this->latestActivity()->status->value);
    }

    #[Test]
    public function a_cancellation_only_removes_what_its_refunds_do_not_cover()
    {
        Bus::fake();

        $this->postJson('/!/shopify/webhook/order/cancelled', [
            'id' => 99,
            'name' => '#1001',
            'cancelled_at' => '2026-03-06T10:00:00Z',
            'line_items' => [
                ['id' => 5, 'product_id' => 10, 'variant_id' => 100, 'sku' => 's', 'quantity' => 3, 'title' => 'Shirt'],
                ['id' => 6, 'product_id' => 20, 'variant_id' => 200, 'sku' => 'h', 'quantity' => 2, 'title' => 'Hat'],
            ],
            'refunds' => [
                ['refund_line_items' => [
                    ['line_item_id' => 5, 'quantity' => 3],
                    ['line_item_id' => 6, 'quantity' => 1],
                ]],
            ],
        ], ['X-Shopify-Topic' => 'orders/cancelled'])->assertOk();

        Bus::assertDispatchedTimes(ImportSingleProductJob::class, 1);
        Bus::assertDispatched(ImportSingleProductJob::class, fn ($job) => $job->productId === 20
            && $job->orderData['quantity'] === ['200' => -1]
            && $job->orderData['date']->format('Y-m-d') === '2026-03-06');
    }

    #[Test]
    public function a_cancellation_fully_covered_by_refunds_is_skipped()
    {
        Bus::fake();

        $this->postJson('/!/shopify/webhook/order/cancelled', [
            'id' => 99,
            'cancelled_at' => '2026-03-06T10:00:00Z',
            'line_items' => [['id' => 5, 'product_id' => 10, 'variant_id' => 100, 'quantity' => 1]],
            'refunds' => [['refund_line_items' => [['line_item_id' => 5, 'quantity' => 1]]]],
        ], ['X-Shopify-Topic' => 'orders/cancelled'])->assertOk();

        Bus::assertNotDispatched(ImportSingleProductJob::class);
        $this->assertSame('skipped', $this->latestActivity()->status->value);
    }

    #[Test]
    public function orders_and_refunds_keep_a_net_purchase_history()
    {
        $this->mockShopify($this->productJson());

        $this->postJson('/!/shopify/webhook/order', [
            'created_at' => '2026-03-01T10:00:00Z',
            'line_items' => [['product_id' => 10, 'variant_id' => 100, 'sku' => 's', 'quantity' => 3]],
        ])->assertOk();

        $this->postJson('/!/shopify/webhook/refund/create', [
            'created_at' => '2026-04-02T10:00:00Z',
            'refund_line_items' => [['quantity' => 1, 'line_item' => ['product_id' => 10, 'variant_id' => 100, 'sku' => 's']]],
        ])->assertOk();

        $product = Facades\Entry::query()->where('collection', 'products')->where('product_id', '10')->first();
        $variant = Facades\Entry::query()->where('collection', 'variants')->where('slug', '100')->first();

        foreach ([$product, $variant] as $entry) {
            $totals = $entry->get('total_purchased');

            $this->assertSame(2, $totals['lifetime']);
            $this->assertSame(2, $totals['2026']['total']);
            $this->assertSame(3, $totals['2026']['03'], 'Bought in March');
            $this->assertSame(-1, $totals['2026']['04'], 'Refunded in April');
            $this->assertSame('2026-03-01 10:00:00', $entry->get('last_purchased'), 'A refund is not a purchase');
        }
    }

    #[Test]
    public function an_inventory_update_sets_the_variant_stock_and_the_product_summary()
    {
        $this->makeImportedProduct(0);
        $this->mockShopify($this->inventoryItemJson(5));

        $this->postJson('/!/shopify/webhook/inventory/update', ['inventory_item_id' => 555, 'location_id' => 1, 'available' => 3], ['X-Shopify-Topic' => 'inventory_levels/update'])
            ->assertOk();

        $variant = Facades\Entry::query()->where('collection', 'variants')->where('slug', '100')->first();
        $product = Facades\Entry::query()->where('collection', 'products')->where('slug', 'shirt')->first();

        $this->assertSame(5, $variant->get('inventory_quantity'), 'The total across locations, not the one location in the webhook');
        $this->assertTrue($product->get('in_stock'));

        $activity = $this->latestActivity();
        $this->assertSame('updated', $activity->status->value);
        $this->assertSame('product', $activity->resourceType);
        $this->assertSame('10', $activity->resourceId);
        $this->assertSame('Stock for Small is now 5', $activity->message);
    }

    #[Test]
    public function an_untracked_variant_is_always_purchasable()
    {
        $this->makeImportedProduct(0);
        $this->mockShopify($this->inventoryItemJson(0, tracked: false));

        $this->postJson('/!/shopify/webhook/inventory/update', ['inventory_item_id' => 555], ['X-Shopify-Topic' => 'inventory_levels/update'])->assertOk();

        $this->assertSame('CONTINUE', Facades\Entry::query()->where('collection', 'variants')->where('slug', '100')->first()->get('inventory_policy'));
    }

    #[Test]
    public function an_inventory_update_for_a_variant_that_is_not_imported_is_skipped()
    {
        $this->mockShopify($this->inventoryItemJson(5));

        $this->postJson('/!/shopify/webhook/inventory/update', ['inventory_item_id' => 555], ['X-Shopify-Topic' => 'inventory_levels/update'])->assertOk();

        $this->assertSame('skipped', $this->latestActivity()->status->value);
        $this->assertSame('Variant not imported', $this->latestActivity()->message);
    }

    #[Test]
    public function an_inventory_update_for_an_item_without_a_variant_is_skipped()
    {
        $this->mockShopify('{"data":{"inventoryItem":{"tracked":true,"variant":null}}}');

        $this->postJson('/!/shopify/webhook/inventory/update', ['inventory_item_id' => 555], ['X-Shopify-Topic' => 'inventory_levels/update'])->assertOk();

        $this->assertSame('skipped', $this->latestActivity()->status->value);
    }

    #[Test]
    public function topics_match_what_shopify_sends()
    {
        $this->assertSame('inventory_levels/update', WebhookTopic::InventoryLevelsUpdate->topic());
        $this->assertSame('orders/cancelled', WebhookTopic::OrdersCancelled->topic());
        $this->assertSame('products/update', WebhookTopic::ProductsUpdate->topic());

        // without the header, the topic comes from the route
        $this->mockShopify('{"data":{"inventoryItem":null}}');
        $this->postJson('/!/shopify/webhook/inventory/update', ['inventory_item_id' => 555])->assertOk();

        $this->assertSame('inventory_levels/update', $this->latestActivity()->topic);
    }

    #[Test]
    public function multi_store_sites_re_import_the_product_for_that_store()
    {
        config()->set('shopify.multi_store', [
            'enabled' => true,
            'mode' => 'unified',
            'primary_store' => 'uk',
            'stores' => [
                'uk' => ['url' => 'uk-store.myshopify.com', 'webhook_secret' => 'uk-secret', 'admin_token' => 'uk-token'],
            ],
        ]);

        $this->app->instance('shopify.graphql.uk', tap(\Mockery::mock(Graphql::class), function ($mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: $this->inventoryItemJson(5)));
        }));

        Bus::fake([ImportSingleProductJob::class]);

        $this->postJson('/!/shopify/webhook/inventory/update', ['inventory_item_id' => 555], [
            'X-Shopify-Topic' => 'inventory_levels/update',
            'X-Shopify-Shop-Domain' => 'uk-store.myshopify.com',
        ])->assertOk();

        Bus::assertDispatched(ImportSingleProductJob::class, fn ($job) => $job->productId === 10 && $job->storeHandle === 'uk');
        $this->assertSame('Shirt', $this->latestActivity()->resourceTitle);
    }
}
