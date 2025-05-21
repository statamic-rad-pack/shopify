<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportAllProductsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $data = [];

    public function __construct($data)
    {
        $this->data = $data;

        if ($queue = config('shopify.queue')) {
            $this->onQueue($queue);
        }
    }

    public function handle()
    {
        foreach ($this->data as $product) {
            ImportSingleProductJob::dispatch($product);
        }
    }
}
