<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Auth\User;
use Statamic\Facades;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Jobs\CreateOrUpdateShopifyUser;
use StatamicRadPack\Shopify\Support\Customers;
use StatamicRadPack\Shopify\Tests\TestCase;

class MultiStoreCustomersTest extends TestCase
{
    /**
     * The queries each store's client was sent, keyed by store handle.
     */
    private array $sent = ['uk' => [], 'us' => []];

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('shopify.multi_store', [
            'enabled' => true,
            'mode' => 'unified',
            'primary_store' => 'uk',
            'stores' => [
                'uk' => ['url' => 'uk-store.myshopify.com', 'admin_token' => 'uk-token'],
                'us' => ['url' => 'us-store.myshopify.com', 'admin_token' => 'us-token'],
            ],
        ]);

        foreach (['uk', 'us'] as $store) {
            $this->app->instance("shopify.graphql.{$store}", tap(Mockery::mock(Graphql::class), function ($mock) use ($store) {
                $mock->shouldReceive('query')->andReturnUsing(function ($query) use ($store) {
                    $this->sent[$store][] = $query;

                    return new HttpResponse(status: 200, body: json_encode(['data' => match (true) {
                        str_contains($query['query'], 'customers(first: 1') => ['customers' => ['edges' => []]],
                        str_contains($query['query'], 'customerCreate') => ['customerCreate' => ['customer' => ['id' => 'gid://shopify/Customer/'.($store === 'uk' ? 7001 : 7002)], 'userErrors' => []]],
                        str_contains($query['query'], 'customerUpdate') => ['customerUpdate' => ['customer' => ['id' => 'gid://shopify/Customer/1'], 'userErrors' => []]],
                        default => ['customerAddressCreate' => ['address' => ['id' => 'gid://shopify/MailingAddress/1'], 'userErrors' => []]],
                    }]));
                });
            }));
        }

        // the default client should never be used for customers on a multi-store site
        $this->mock(Graphql::class)->shouldNotReceive('query');
    }

    private function customer(array $shopifyIds = []): User
    {
        $user = Facades\User::make()->email('shopper@example.com')->data(['name' => 'Sam Shopper']);

        if ($shopifyIds) {
            $user->set('shopify_ids', $shopifyIds);
        }

        return tap($user)->saveQuietly();
    }

    private function address(array $extra = []): array
    {
        return ['firstName' => 'a', 'lastName' => 'b', 'address1' => 'c', 'city' => 'd', 'province' => 'e', 'zip' => 'f', 'country' => 'g'] + $extra;
    }

    #[Test]
    public function saves_an_address_to_the_customer_in_the_store_the_form_was_for()
    {
        $this->actingAs($this->customer(['uk' => 111, 'us' => 222]));

        $this->postJson('/!/shopify/address', $this->address(['_store' => 'us']))->assertOk();

        $this->assertSame([], $this->sent['uk']);
        $this->assertSame('gid://shopify/Customer/222', $this->sent['us'][0]['variables']['customerId']);
    }

    #[Test]
    public function needs_a_store_to_find_the_customer_on_a_multi_store_site()
    {
        $this->actingAs($this->customer(['us' => 222]));

        $this->postJson('/!/shopify/address', $this->address())
            ->assertStatus(422)
            ->assertJson(['message' => 'No Shopify customer is associated with the current user']);

        $this->postJson('/!/shopify/address', $this->address(['_store' => 'uk']))->assertStatus(422);
        $this->postJson('/!/shopify/address', $this->address(['_store' => 'not-a-store']))->assertStatus(422);

        $this->assertSame([], $this->sent['uk']);
        $this->assertSame([], $this->sent['us']);
    }

    #[Test]
    public function the_address_form_posts_its_store()
    {
        $html = (string) Facades\Parse::template('{{ shopify:address_form store="us" }}{{ /shopify:address_form }}', [], trusted: true);
        $this->assertStringContainsString('<input type="hidden" name="_store" value="us" />', $html);
        $this->assertStringNotContainsString('store="us"', $html, 'The param is not rendered as a form attribute');

        $html = (string) Facades\Parse::template('{{ shopify:address_form store="nope" }}{{ /shopify:address_form }}', [], trusted: true);
        $this->assertStringNotContainsString('_store', $html);
    }

    #[Test]
    public function updates_the_customer_in_each_store_the_user_is_linked_to()
    {
        $user = $this->customer(['uk' => 111, 'us' => 222]);

        CreateOrUpdateShopifyUser::dispatch($user);

        $this->assertSame('gid://shopify/Customer/111', $this->sent['uk'][0]['variables']['input']['id']);
        $this->assertSame('gid://shopify/Customer/222', $this->sent['us'][0]['variables']['input']['id']);
        $this->assertSame('Sam', $this->sent['us'][0]['variables']['input']['firstName']);
    }

    #[Test]
    public function creates_a_new_customer_in_the_customer_store_only()
    {
        config()->set('shopify.multi_store.customer_store', 'us');
        $user = $this->customer();

        CreateOrUpdateShopifyUser::dispatch($user);

        $this->assertSame([], $this->sent['uk']);
        $this->assertSame(['us' => 7002], $user->fresh()->get('shopify_ids'));
        $this->assertNull($user->fresh()->get('shopify_id'));
    }

    #[Test]
    public function creates_a_new_customer_in_the_primary_store_by_default()
    {
        $user = $this->customer();

        CreateOrUpdateShopifyUser::dispatch($user);

        $this->assertSame([], $this->sent['us']);
        $this->assertNotEmpty($this->sent['uk']);
        $this->assertSame(['uk' => 7001], $user->fresh()->get('shopify_ids'));
    }

    #[Test]
    public function does_not_create_customers_without_a_store_to_create_them_in()
    {
        config()->set('shopify.multi_store.primary_store', null);
        $user = $this->customer();

        CreateOrUpdateShopifyUser::dispatch($user);

        $this->assertSame([], $this->sent['uk']);
        $this->assertSame([], $this->sent['us']);
        $this->assertNull($user->fresh()->get('shopify_ids'));
    }

    #[Test]
    public function markets_mode_uses_the_single_store_customer_id()
    {
        config()->set('shopify.multi_store', ['enabled' => true, 'mode' => 'markets', 'markets' => ['GB' => [], 'US' => []], 'stores' => []]);

        $user = Facades\User::make()->email('a@b.com')->set('shopify_id', 5);

        $this->assertFalse(Customers::perStore());
        $this->assertSame('5', Customers::id($user, 'GB'));
        $this->assertSame([null], Customers::syncStores($user));
        $this->assertSame(6, Customers::setId($user, 'GB', 6)->get('shopify_id'));
    }
}
