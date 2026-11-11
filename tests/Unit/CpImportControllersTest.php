<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Jobs\ImportAllCollectionsJob;
use StatamicRadPack\Shopify\Jobs\ImportAllProductsJob;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
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
            'import all products' => ['shopify.products.fetchAll', 'postJson'],
            'import single product' => ['shopify.products.fetch', 'postJson'],
            'import all collections' => ['shopify.collections.fetchAll', 'postJson'],
            'list products' => ['shopify.products', 'getJson'],
        ];
    }

    #[Test]
    #[DataProvider('guardedEndpoints')]
    public function endpoints_require_the_access_shopify_permission(string $route, string $method)
    {
        $this->actingAsPlainUser()
            ->{$method}(cp_route($route))
            ->assertForbidden();
    }

    #[Test]
    public function a_permitted_user_can_trigger_a_single_product_import()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(status: 200, body: '{"data":{}}'));
        });

        $this->actingAsSuperUser()
            ->postJson(cp_route('shopify.products.fetch'), ['product' => 1])
            ->assertOk()
            ->assertJsonPath('message', 'Product import has been queued.');
    }

    #[Test]
    public function importing_all_products_and_collections_is_queued()
    {
        Bus::fake();

        $this->actingAsSuperUser()
            ->postJson(cp_route('shopify.products.fetchAll'))
            ->assertOk();

        $this->actingAsSuperUser()
            ->postJson(cp_route('shopify.collections.fetchAll'))
            ->assertOk();

        Bus::assertDispatched(ImportAllProductsJob::class);
        Bus::assertDispatched(ImportAllCollectionsJob::class);
    }

    #[Test]
    public function the_import_all_products_job_queues_each_product()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')->andReturn(new HttpResponse(
                status: 200,
                body: '{"data":{"products":{"nodes":[{"id":"gid://shopify/Product/1"},{"id":"gid://shopify/Product/2"}],"pageInfo":{"hasNextPage":false,"endCursor":null}}}}'
            ));
        });

        Bus::fake([ImportSingleProductJob::class]);

        (new ImportAllProductsJob)->handle();

        Bus::assertDispatchedTimes(ImportSingleProductJob::class, 2);
    }

    #[Test]
    public function import_endpoints_do_not_accept_get_requests()
    {
        Bus::fake();

        $this->actingAsSuperUser()->getJson(cp_route('shopify.products.fetchAll'));
        $this->actingAsSuperUser()->getJson(cp_route('shopify.collections.fetchAll'));

        Bus::assertNotDispatched(ImportAllProductsJob::class);
        Bus::assertNotDispatched(ImportAllCollectionsJob::class);
    }
}
