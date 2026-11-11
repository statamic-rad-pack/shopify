<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Jobs;
use StatamicRadPack\Shopify\Tests\TestCase;

class CreateOrUpdateShopifyUserJobTest extends TestCase
{
    #[Test]
    public function attaches_a_shopify_id_for_an_existing_shopify_user()
    {
        $user = tap(Facades\User::make()
            ->email('test@test.com'))
            ->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) {
                    return str_contains($query['query'], 'customers(first: 1, query: $query)')
                        && $query['variables']['query'] === 'email:test@test.com';
                })
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "customers": {
                          "edges": [
                            {
                              "node": {
                                "id": "gid://shopify/Customer/1"
                              }
                            }
                          ]
                        }
                      }
                    }'
                ));
        });

        $this->assertNull($user->get('shopify_id'));

        Jobs\CreateOrUpdateShopifyUser::dispatch($user);

        $this->assertNotNull($user->fresh()->get('shopify_id'));
        $this->assertSame(1, $user->fresh()->get('shopify_id'));
    }

    #[Test]
    public function attaches_a_shopify_id_when_creating_a_new_shopify_user()
    {
        $user = tap(Facades\User::make()
            ->email('test@test.com'))
            ->save();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) {
                    return str_contains($query['query'], 'customers(first: 1, query: $query)')
                        && $query['variables']['query'] === 'email:test@test.com';
                })
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "customers": {
                          "edges": []
                        }
                      }
                    }'
                ));

            $mock
                ->shouldReceive('query')
                ->withArgs(function ($query) {
                    return str_contains($query['query'], 'mutation customerCreate($input: CustomerInput!)')
                        && $query['variables']['input']['email'] === 'test@test.com';
                })
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "customerCreate": {
                            "customer": {
                                "id": "gid://shopify/Customer/1"
                            }
                        }
                      }
                    }'
                ));
        });

        $this->assertNull($user->get('shopify_id'));

        Jobs\CreateOrUpdateShopifyUser::dispatch($user);

        $this->assertNotNull($user->fresh()->get('shopify_id'));
        $this->assertSame(1, $user->fresh()->get('shopify_id'));
    }

    #[Test]
    public function sends_the_customer_id_when_updating_a_shopify_user()
    {
        $user = tap(Facades\User::make()->email('test@test.com')->set('shopify_id', 123))->saveQuietly();

        $captured = null;

        $this->mock(Graphql::class, function (MockInterface $mock) use (&$captured) {
            $mock
                ->shouldReceive('query')
                ->andReturnUsing(function ($args) use (&$captured) {
                    $captured = $args;

                    return new HttpResponse(status: 200, body: '{"data":{"customerUpdate":{"customer":{"id":"gid://shopify/Customer/123"},"userErrors":[]}}}');
                });
        });

        Jobs\CreateOrUpdateShopifyUser::dispatch($user);

        $this->assertStringContainsString('customerUpdate', $captured['query']);
        $this->assertSame('gid://shopify/Customer/123', $captured['variables']['input']['id']);
    }

    #[Test]
    public function throws_shopify_user_errors_when_updating_a_shopify_user()
    {
        $user = tap(Facades\User::make()->email('test@test.com')->set('shopify_id', 123))->saveQuietly();

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->andReturn(new HttpResponse(status: 200, body: '{"data":{"customerUpdate":{"customer":null,"userErrors":[{"field":["email"],"message":"Email has already been taken"}]}}}'));
        });

        $this->expectExceptionMessage('Email has already been taken');

        (new Jobs\CreateOrUpdateShopifyUser($user))->handle();
    }
}
