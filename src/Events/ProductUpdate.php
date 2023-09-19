<?php

namespace StatamicRadPack\Shopify\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductUpdate
{
    use Dispatchable, SerializesModels;

    public function __construct(public array $data)
    {
    }
}
