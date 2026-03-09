<?php

namespace StatamicRadPack\Shopify\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProductImportFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $productId,
        public ?string $storeHandle,
        public Throwable $exception,
    ) {}
}
