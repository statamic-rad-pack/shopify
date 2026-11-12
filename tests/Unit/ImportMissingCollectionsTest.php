<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Contracts\ActivityRepository;
use StatamicRadPack\Shopify\Jobs\ImportCollectionJob;
use StatamicRadPack\Shopify\Tests\TestCase;

class ImportMissingCollectionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['collections', 'tags', 'type', 'vendor'] as $taxonomy) {
            Facades\Taxonomy::make($taxonomy)->save();
        }

        // imported earlier, so it has Shopify's data
        Facades\Term::make()->taxonomy('collections')->slug('shoes')->data(['title' => 'Shoes', 'collection_id' => 11])->save();
    }

    private function productJson(int $id, array $collections): string
    {
        return json_encode(['data' => ['product' => [
            'id' => "gid://shopify/Product/{$id}",
            'handle' => "product-{$id}",
            'title' => "Product {$id}",
            'descriptionHtml' => '',
            'tags' => [],
            'vendor' => '',
            'productType' => '',
            'options' => [],
            'collections' => ['edges' => collect($collections)->map(fn ($collectionId, $handle) => ['node' => ['id' => "gid://shopify/Collection/{$collectionId}", 'handle' => $handle]])->values()->all()],
            'metafields' => ['edges' => []],
            'media' => ['edges' => []],
            'resourcePublications' => ['edges' => []],
            'variants' => ['edges' => [
                ['node' => ['id' => "gid://shopify/ProductVariant/{$id}0", 'title' => 'Default', 'price' => '10.00', 'compareAtPrice' => null, 'sku' => '', 'inventoryQuantity' => 1, 'inventoryPolicy' => 'DENY', 'inventoryItem' => ['tracked' => true], 'selectedOptions' => []]],
            ]],
        ]]]);
    }

    private function collectionJson(): string
    {
        return json_encode(['data' => ['collection' => [
            'id' => 'gid://shopify/Collection/77',
            'handle' => 'summer',
            'title' => 'Summer Sale',
            'descriptionHtml' => '<p>Hot deals</p>',
            'metafields' => ['edges' => []],
        ]]]);
    }

    private function mockShopify(array $products): void
    {
        $this->mock(Graphql::class, function (MockInterface $mock) use ($products) {
            $mock->shouldReceive('query')->andReturnUsing(function ($query) use ($products) {
                if (str_contains($query['query'], 'collection(id:')) {
                    return new HttpResponse(status: 200, body: $this->collectionJson());
                }

                preg_match('/Product\/(\d+)/', $query['query'], $match);

                return new HttpResponse(status: 200, body: $products[(int) ($match[1] ?? 0)] ?? '{"data":{}}');
            });
        });
    }

    private function product(int $id)
    {
        return Facades\Entry::query()->where('collection', 'products')->where('product_id', (string) $id)->first();
    }

    #[Test]
    public function queues_an_import_for_a_collection_that_has_not_been_imported()
    {
        $this->mockShopify([1 => $this->productJson(1, ['shoes' => 11, 'summer' => 77])]);
        Bus::fake([ImportCollectionJob::class]);

        $productActivity = ActivityLog::queueProductImport(1, source: Activity::SOURCE_WEBHOOK);

        $this->assertSame(['shoes', 'summer'], $this->product(1)->get('collections'), 'The product keeps all its collections');

        Bus::assertDispatchedTimes(ImportCollectionJob::class, 1);
        Bus::assertDispatched(ImportCollectionJob::class, fn ($job) => $job->collectionId === 77 && $job->activityId !== null);

        $collectionActivity = collect(app(ActivityRepository::class)->paginate(['resource_type' => 'collection'], 10)->items())->first();
        $this->assertSame($productActivity, $collectionActivity->parentId);
        $this->assertSame('webhook', $collectionActivity->source, 'Same source as the product import that found it');
        $this->assertSame('summer', $collectionActivity->resourceTitle);
        $this->assertSame('New collection found on product Product 1', $collectionActivity->message);
    }

    #[Test]
    public function queues_each_new_collection_once_for_products_imported_together()
    {
        $this->mockShopify([
            1 => $this->productJson(1, ['summer' => 77]),
            2 => $this->productJson(2, ['summer' => 77]),
        ]);
        Bus::fake([ImportCollectionJob::class]);

        ActivityLog::queueProductImport(1);
        ActivityLog::queueProductImport(2);

        Bus::assertDispatchedTimes(ImportCollectionJob::class, 1);
        $this->assertSame(['summer'], $this->product(2)->get('collections'));
    }

    #[Test]
    public function imports_the_collection_and_the_product_shows_it()
    {
        $this->mockShopify([1 => $this->productJson(1, ['shoes' => 11, 'summer' => 77])]);

        ActivityLog::queueProductImport(1);

        $term = Facades\Term::query()->where('taxonomy', 'collections')->where('slug', 'summer')->first();
        $this->assertSame('Summer Sale', $term->title());
        $this->assertSame(77, $term->get('collection_id'));

        $this->assertSame(
            ['Shoes', 'Summer Sale'],
            collect($this->product(1)->get('collections'))->map(fn ($slug) => Facades\Term::find("collections::{$slug}")->title())->all()
        );
    }

    #[Test]
    public function only_assigns_imported_collections_when_turned_off()
    {
        config()->set('shopify.import_missing_collections', false);

        // made by hand, without Shopify's data
        Facades\Term::make()->taxonomy('collections')->slug('staff-picks')->data(['title' => 'Staff picks'])->save();

        $this->mockShopify([1 => $this->productJson(1, ['shoes' => 11, 'staff-picks' => 12, 'summer' => 77])]);
        Bus::fake([ImportCollectionJob::class]);

        ActivityLog::queueProductImport(1);

        $this->assertSame(['shoes', 'staff-picks'], $this->product(1)->get('collections'));
        Bus::assertNotDispatched(ImportCollectionJob::class);
    }
}
