<?php

namespace StatamicRadPack\Shopify\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use stdClass;

class ProductDelete
{
    use Dispatchable, SerializesModels;

    public function __construct(public stdClass $data)
    {
    }
}
