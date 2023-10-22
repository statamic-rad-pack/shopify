<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Closure;
use Illuminate\Http\Request;
use StatamicRadPack\Shopify\Events;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;

class ProductCreateUpdateController extends WebhooksController
{
    public function create(Request $request)
    {
        return $this->processWebhook($request, fn($data) => Events\ProductCreate::dispatch($data));
    }

    public function update(Request $request)
    {
        return $this->processWebhook($request, fn($data) => Events\ProductUpdate::dispatch($data));
    }

    private function processWebhook(Request $request, Closure $eventCallback)
    {
        // Decode data
        $dataArray = json_decode($request->getContent(), true);
        $data = json_decode($request->getContent());

        // Dispatch job
        ImportSingleProductJob::dispatch($dataArray)->onQueue(config('shopify.queue'));

        $eventCallback($data);

        return response()->json([
            'message' => 'Product has been dispatched to the queue for update',
        ], 200);
    }
}
