<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Tests\TestCase;

/**
 * Markets mode is one Shopify store, so its webhooks are handled as in single-store mode.
 */
class MultiStoreMarketsWebhooksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('shopify.webhook_secret', 'secret');
        config()->set('shopify.multi_store', [
            'enabled' => true,
            'mode' => 'markets',
            'markets' => ['GB' => ['currency' => '£'], 'US' => ['currency' => '$']],
            'stores' => [],
        ]);
    }

    private function signed(string $uri, array $payload, string $topic)
    {
        $body = json_encode($payload);

        return $this->call('POST', $uri, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SHOPIFY_HMAC_SHA256' => base64_encode(hash_hmac('sha256', $body, 'secret', true)),
            'HTTP_X_SHOPIFY_SHOP_DOMAIN' => 'my-store.myshopify.com',
            'HTTP_X_SHOPIFY_TOPIC' => $topic,
        ], $body);
    }

    #[Test]
    public function accepts_webhooks_from_the_store_with_its_domain_header()
    {
        Bus::fake();

        $this->signed('/!/shopify/webhook/product/update', ['id' => 10], 'products/update')->assertOk();

        Bus::assertDispatched(ImportSingleProductJob::class, fn ($job) => $job->productId === 10 && $job->storeHandle === null);
    }

    #[Test]
    public function links_customers_with_the_single_store_id()
    {
        $user = tap(User::make()->email('a@b.com'))->save();

        $this->signed('/!/shopify/webhook/customer/update', ['id' => 55, 'email' => 'a@b.com'], 'customers/update')->assertOk();

        $this->assertSame(55, $user->fresh()->get('shopify_id'));
        $this->assertNull($user->fresh()->get('shopify_ids'));
    }
}
