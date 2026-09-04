<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Statamic\Facades\User;
use StatamicRadPack\Shopify\Tests\TestCase;

class CpImportControllersTest extends TestCase
{
    private function actingAsSuperUser()
    {
        return $this->actingAs(User::make()->email('admin@example.com')->makeSuper()->save());
    }

    private function actingAsPlainUser()
    {
        return $this->actingAs(tap(User::make()->email('nobody@example.com'))->save());
    }

    public static function guardedEndpoints(): array
    {
        return [
            'import all products' => ['shopify.products.fetchAll'],
            'import single product' => ['shopify.products.fetch'],
            'import all collections' => ['shopify.collections.fetchAll'],
            'list products' => ['shopify.products'],
        ];
    }

    #[Test]
    #[DataProvider('guardedEndpoints')]
    public function endpoints_require_the_access_shopify_permission(string $route)
    {
        $this->actingAsPlainUser()
            ->getJson(cp_route($route))
            ->assertForbidden();
    }

    #[Test]
    public function a_permitted_user_can_trigger_a_single_product_import()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: '{"data":{}}'));
        });

        $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.products.fetch', ['product' => 1]))
            ->assertOk()
            ->assertJsonPath('message', 'Product import has been queued.');
    }
}
