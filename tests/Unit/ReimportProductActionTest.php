<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Actions\ReimportProduct;
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
    public function is_not_visible_on_list_view()
    {
        $entry = $this->makeProductEntry();

        $this->assertFalse($this->actionWithContext('list')->visibleTo($entry));
    }

    #[Test]
    public function is_not_visible_on_non_product_entry()
    {
        $entry = $this->makeVariantEntry();

        $this->assertFalse($this->actionWithContext('form')->visibleTo($entry));
    }

    #[Test]
    public function is_not_visible_in_bulk()
    {
        $this->assertFalse($this->actionWithContext()->visibleToBulk(collect()));
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
