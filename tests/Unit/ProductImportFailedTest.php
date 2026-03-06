<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use StatamicRadPack\Shopify\Events\ProductImportFailed;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Tests\TestCase;

class ProductImportFailedTest extends TestCase
{
    #[Test]
    public function logs_error_on_failure()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return str_contains($message, '12345')
                    && $context['product_id'] === 12345;
            });

        $job = new ImportSingleProductJob(12345);
        $job->failed(new RuntimeException('Something went wrong'));
    }

    #[Test]
    public function includes_store_handle_in_log_context_when_present()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return $context['product_id'] === 12345
                    && $context['store'] === 'uk';
            });

        $job = new ImportSingleProductJob(12345, [], 'uk');
        $job->failed(new RuntimeException('Something went wrong'));
    }

    #[Test]
    public function omits_store_from_log_context_in_single_store_mode()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return ! array_key_exists('store', $context);
            });

        $job = new ImportSingleProductJob(12345);
        $job->failed(new RuntimeException('Something went wrong'));
    }

    #[Test]
    public function fires_product_import_failed_event()
    {
        Event::fake();
        Log::shouldReceive('error')->once();

        $exception = new RuntimeException('Something went wrong');

        $job = new ImportSingleProductJob(12345);
        $job->failed($exception);

        Event::assertDispatched(ProductImportFailed::class, function ($event) use ($exception) {
            return $event->productId === 12345
                && $event->storeHandle === null
                && $event->exception === $exception;
        });
    }

    #[Test]
    public function event_carries_store_handle_in_multi_store_mode()
    {
        Event::fake();
        Log::shouldReceive('error')->once();

        $job = new ImportSingleProductJob(99, [], 'uk');
        $job->failed(new RuntimeException('Timeout'));

        Event::assertDispatched(ProductImportFailed::class, function ($event) {
            return $event->productId === 99
                && $event->storeHandle === 'uk';
        });
    }
}
