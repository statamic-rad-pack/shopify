<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Jobs\ImportCollectionJob;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Tests\TestCase;

class MultiStoreWebhooksTest extends TestCase
{
    private function multiStoreConfig(): array
    {
        return [
            'enabled' => true,
            'mode' => 'unified',
            'primary_store' => 'uk',
            'stores' => [
                'uk' => [
                    'url' => 'uk-store.myshopify.com',
                    'webhook_secret' => 'uk-secret',
                    'admin_token' => 'uk-token',
                    'storefront_token' => 'uk-storefront-token',
                    'api_version' => '2025-04',
                ],
                'us' => [
                    'url' => 'us-store.myshopify.com',
                    'webhook_secret' => 'us-secret',
                    'admin_token' => 'us-token',
                    'storefront_token' => 'us-storefront-token',
                    'api_version' => '2025-04',
                ],
            ],
        ];
    }

    #[Test]
    public function uses_per_store_webhook_secret_for_hmac_verification()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());

        $payload = '{"id":788032119674292922}';
        $hmac = base64_encode(hash_hmac('sha256', $payload, 'uk-secret', true));

        $response = $this->postJson(
            '/!/shopify/webhook/product/delete',
            json_decode($payload, true),
            [
                'X-Shopify-Hmac-Sha256' => $hmac,
                'X-Shopify-Shop-Domain' => 'uk-store.myshopify.com',
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function rejects_wrong_store_secret()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());

        $payload = '{"id":788032119674292922}';
        // Use the US secret but claim to be the UK store — HMAC should fail
        $hmac = base64_encode(hash_hmac('sha256', $payload, 'us-secret', true));

        $response = $this->postJson(
            '/!/shopify/webhook/product/delete',
            json_decode($payload, true),
            [
                'X-Shopify-Hmac-Sha256' => $hmac,
                'X-Shopify-Shop-Domain' => 'uk-store.myshopify.com',
            ]
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    #[Test]
    public function rejects_requests_with_unknown_domain()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());

        $payload = '{"id":788032119674292922}';
        $hmac = base64_encode(hash_hmac('sha256', $payload, 'some-secret', true));

        $response = $this->postJson(
            '/!/shopify/webhook/product/delete',
            json_decode($payload, true),
            [
                'X-Shopify-Hmac-Sha256' => $hmac,
                'X-Shopify-Shop-Domain' => 'unknown-store.myshopify.com',
            ]
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    #[Test]
    public function passes_store_handle_to_dispatched_product_create_job()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());
        config()->set('shopify.ignore_webhook_integrity_check', true);

        Bus::fake();

        $payload = '{"id":788032119674292922}';

        $this->postJson(
            '/!/shopify/webhook/product/create',
            json_decode($payload, true),
            ['X-Shopify-Shop-Domain' => 'uk-store.myshopify.com']
        );

        Bus::assertDispatched(ImportSingleProductJob::class, function ($job) {
            return $job->storeHandle === 'uk' && $job->productId === 788032119674292922;
        });
    }

    #[Test]
    public function passes_store_handle_to_dispatched_product_update_job()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());
        config()->set('shopify.ignore_webhook_integrity_check', true);

        Bus::fake();

        $payload = '{"id":788032119674292922}';

        $this->postJson(
            '/!/shopify/webhook/product/update',
            json_decode($payload, true),
            ['X-Shopify-Shop-Domain' => 'us-store.myshopify.com']
        );

        Bus::assertDispatched(ImportSingleProductJob::class, function ($job) {
            return $job->storeHandle === 'us';
        });
    }

    #[Test]
    public function passes_store_handle_to_dispatched_order_jobs()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());
        config()->set('shopify.ignore_webhook_integrity_check', true);

        Bus::fake();

        $payload = json_encode([
            'created_at' => '2024-01-01T00:00:00Z',
            'line_items' => [
                ['product_id' => 123456, 'sku' => 'sku-1', 'quantity' => 1],
            ],
        ]);

        $this->postJson(
            '/!/shopify/webhook/order',
            json_decode($payload, true),
            ['X-Shopify-Shop-Domain' => 'uk-store.myshopify.com']
        );

        Bus::assertDispatched(ImportSingleProductJob::class, function ($job) {
            return $job->storeHandle === 'uk' && $job->productId === 123456;
        });
    }

    #[Test]
    public function passes_store_handle_to_dispatched_collection_job()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());
        config()->set('shopify.ignore_webhook_integrity_check', true);

        Bus::fake();

        $payload = '{"id":999}';

        $this->postJson(
            '/!/shopify/webhook/collection/create',
            json_decode($payload, true),
            ['X-Shopify-Shop-Domain' => 'uk-store.myshopify.com']
        );

        Bus::assertDispatched(ImportCollectionJob::class, function ($job) {
            return $job->storeHandle === 'uk' && $job->collectionId === 999;
        });
    }

    #[Test]
    public function single_store_mode_still_works_without_domain_header()
    {
        // multi_store disabled (default)
        config()->set('shopify.ignore_webhook_integrity_check', true);

        Bus::fake();

        $payload = '{"id":788032119674292922}';

        $response = $this->postJson(
            '/!/shopify/webhook/product/create',
            json_decode($payload, true)
        );

        $this->assertEquals(200, $response->getStatusCode());

        Bus::assertDispatched(ImportSingleProductJob::class, function ($job) {
            return $job->storeHandle === null;
        });
    }

    #[Test]
    public function product_delete_in_unified_mode_only_removes_that_stores_data()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());
        config()->set('shopify.ignore_webhook_integrity_check', true);

        Facades\Collection::make(config('shopify.collection_handle', 'products'))->save();
        Facades\Collection::make('variants')->save();

        tap(Facades\Entry::make()->collection(config('shopify.collection_handle', 'products'))->slug('obi-wan')->data(['product_id' => 5]))->save();
        tap(Facades\Entry::make()->collection('variants')->slug('50')->data([
            'product_slug' => 'obi-wan',
            'multi_store_data' => ['uk' => ['price' => 10], 'us' => ['price' => 12]],
        ]))->save();

        $this->postJson('/!/shopify/webhook/product/delete', ['id' => 5], ['X-Shopify-Shop-Domain' => 'us-store.myshopify.com'])
            ->assertStatus(200);

        $this->assertCount(1, Facades\Entry::whereCollection(config('shopify.collection_handle', 'products')));
        $variant = Facades\Entry::whereCollection('variants')->first();
        $this->assertSame(['uk' => ['price' => 10]], $variant->get('multi_store_data'));

        // once the last store deletes it, the entries go
        $this->postJson('/!/shopify/webhook/product/delete', ['id' => 5], ['X-Shopify-Shop-Domain' => 'uk-store.myshopify.com'])
            ->assertStatus(200);

        $this->assertCount(0, Facades\Entry::whereCollection(config('shopify.collection_handle', 'products')));
        $this->assertCount(0, Facades\Entry::whereCollection('variants'));
    }

    #[Test]
    public function customer_delete_removes_only_that_stores_shopify_id()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());
        config()->set('shopify.ignore_webhook_integrity_check', true);

        $user = tap(Facades\User::make()->email('a@b.com')->set('shopify_ids', ['uk' => 1, 'us' => 2]))->save();

        $this->postJson('/!/shopify/webhook/customer/delete', ['id' => 2], ['X-Shopify-Shop-Domain' => 'us-store.myshopify.com'])
            ->assertStatus(200);

        $this->assertSame(['uk' => 1], $user->fresh()->get('shopify_ids'));
    }
}
