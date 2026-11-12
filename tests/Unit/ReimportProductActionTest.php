<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Actions\ReimportProduct;
use StatamicRadPack\Shopify\Contracts\ActivityRepository;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Tests\TestCase;

class ReimportProductActionTest extends TestCase
{
    private function makeProductEntry(int $productId = 123, string $locale = 'default')
    {
        $entry = Facades\Entry::make()
            ->collection(config('shopify.collection_handle', 'products'))
            ->locale($locale)
            ->slug('test-product')
            ->data(['product_id' => $productId]);

        $entry->save();

        return $entry;
    }

    private function makeVariantEntry()
    {
        $entry = Facades\Entry::make()
            ->collection('variants')
            ->slug('test-variant')
            ->data(['product_id' => 999]);

        $entry->save();

        return $entry;
    }

    private function actionWithContext(string $view = 'form'): ReimportProduct
    {
        return (new ReimportProduct)->context(['view' => $view]);
    }

    #[Test]
    public function is_visible_on_product_entry_form_view()
    {
        $entry = $this->makeProductEntry();

        $this->assertTrue($this->actionWithContext('form')->visibleTo($entry));
    }

    #[Test]
    public function is_visible_on_product_entries_in_the_listing()
    {
        $entry = $this->makeProductEntry();

        $this->assertTrue($this->actionWithContext('list')->visibleTo($entry));
    }

    #[Test]
    public function is_not_visible_on_products_without_a_shopify_id()
    {
        $entry = Facades\Entry::make()->collection(config('shopify.collection_handle', 'products'))->slug('manual')->data(['title' => 'Manual']);
        $entry->save();

        $this->assertFalse($this->actionWithContext('list')->visibleTo($entry));
    }

    #[Test]
    public function is_not_visible_on_non_product_entry()
    {
        $entry = $this->makeVariantEntry();

        $this->assertFalse($this->actionWithContext('form')->visibleTo($entry));
    }

    #[Test]
    public function is_visible_in_bulk_only_when_every_item_is_a_product()
    {
        $product = $this->makeProductEntry();
        $other = $this->makeProductEntry(456);
        $variant = $this->makeVariantEntry();

        $this->assertTrue($this->actionWithContext('list')->visibleToBulk(collect([$product, $other])));
        $this->assertFalse($this->actionWithContext('list')->visibleToBulk(collect([$product, $variant])));
        $this->assertFalse($this->actionWithContext('list')->visibleToBulk(collect()));
    }

    #[Test]
    public function reimports_several_products_and_reports_how_many_were_queued()
    {
        Queue::fake();

        $entries = collect([123, 456, 789])->map(fn ($id) => $this->makeProductEntry($id));

        $message = $this->actionWithContext('list')->run($entries, []);

        Queue::assertPushed(ImportSingleProductJob::class, 3);
        $this->assertSame('3 products queued for reimport. You can follow them under Shopify > Activity.', $message);

        $activity = app(ActivityRepository::class)->paginate(['source' => 'cp'])->items();
        $this->assertSame(['123', '456', '789'], collect($activity)->pluck('resourceId')->sort()->values()->all());
    }

    #[Test]
    public function reimports_each_product_once_and_skips_entries_without_a_shopify_id()
    {
        Queue::fake();

        $product = $this->makeProductEntry(123);
        $sameProduct = $this->makeProductEntry(123);
        $manual = Facades\Entry::make()->collection(config('shopify.collection_handle', 'products'))->slug('manual')->data(['title' => 'Manual']);
        $manual->save();

        $message = $this->actionWithContext('list')->run(collect([$product, $sameProduct, $manual]), []);

        Queue::assertPushed(ImportSingleProductJob::class, 1);
        $this->assertSame('1 product queued for reimport. You can follow it under Shopify > Activity.', $message);
    }

    #[Test]
    public function can_be_run_in_bulk_from_the_entries_listing()
    {
        Queue::fake();

        $this->actingAs(tap(Facades\User::make()->email('admin@example.com')->makeSuper())->save());

        $ids = collect([123, 456])->map(fn ($id) => $this->makeProductEntry($id)->id())->all();

        $this->postJson(cp_route('collections.entries.actions.run', config('shopify.collection_handle', 'products')), [
            'action' => 'reimport_product',
            'selections' => $ids,
            'values' => [],
            'context' => ['view' => 'list'],
        ])
            ->assertOk()
            ->assertJsonPath('message', '2 products queued for reimport. You can follow them under Shopify > Activity.');

        Queue::assertPushed(ImportSingleProductJob::class, 2);
    }

    #[Test]
    public function dispatches_import_job_with_product_id()
    {
        Queue::fake();

        $entry = $this->makeProductEntry(productId: 456);

        $this->actionWithContext()->run(collect([$entry]), []);

        Queue::assertPushed(ImportSingleProductJob::class, function ($job) {
            return $job->productId === 456 && $job->storeHandle === null;
        });
    }

    #[Test]
    public function dispatches_job_without_store_handle_in_single_store_mode()
    {
        Queue::fake();

        config(['shopify.multi_store.enabled' => false]);

        $entry = $this->makeProductEntry(productId: 789);

        $this->actionWithContext()->run(collect([$entry]), []);

        Queue::assertPushed(ImportSingleProductJob::class, function ($job) {
            return $job->productId === 789 && $job->storeHandle === null;
        });
    }

    #[Test]
    public function dispatches_job_with_store_handle_in_localized_multi_store_mode()
    {
        Queue::fake();

        Facades\Site::setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);

        Facades\Collection::find(config('shopify.collection_handle', 'products'))->sites(['en', 'fr'])->save();

        config(['shopify.multi_store' => [
            'enabled' => true,
            'mode' => 'localized',
            'primary_store' => 'uk',
            'stores' => [
                'uk' => ['url' => 'uk.myshopify.com', 'admin_token' => 'tok', 'site' => 'en'],
                'fr' => ['url' => 'fr.myshopify.com', 'admin_token' => 'tok', 'site' => 'fr'],
            ],
        ]]);

        $entry = Facades\Entry::make()
            ->collection(config('shopify.collection_handle', 'products'))
            ->locale('fr')
            ->slug('test-product-fr')
            ->data(['product_id' => 101]);

        $entry->save();

        $this->actionWithContext()->run(collect([$entry]), []);

        Queue::assertPushed(ImportSingleProductJob::class, function ($job) {
            return $job->productId === 101 && $job->storeHandle === 'fr';
        });
    }

    #[Test]
    public function dispatches_job_without_store_handle_in_unified_multi_store_mode()
    {
        Queue::fake();

        config(['shopify.multi_store' => [
            'enabled' => true,
            'mode' => 'unified',
            'primary_store' => 'uk',
            'stores' => [
                'uk' => ['url' => 'uk.myshopify.com', 'admin_token' => 'tok', 'site' => 'en'],
            ],
        ]]);

        $entry = $this->makeProductEntry(productId: 202);

        $this->actionWithContext()->run(collect([$entry]), []);

        Queue::assertPushed(ImportSingleProductJob::class, function ($job) {
            return $job->productId === 202 && $job->storeHandle === null;
        });
    }
}
