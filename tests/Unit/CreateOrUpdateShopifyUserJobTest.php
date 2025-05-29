<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Statamic\Facades;
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
                    return str_contains($query['query'], 'query: "email:');
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
                    return str_contains($query['query'], 'query: "email:');
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
                    return str_contains($query['query'], 'mutation customerCreate');
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
}
