<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Closure;
use Illuminate\Http\Request;
use StatamicRadPack\Shopify\Events;
use StatamicRadPack\Shopify\Jobs;

class CollectionCreateUpdateController extends WebhooksController
{
    public function create(Request $request)
    {
        return $this->processWebhook($request, fn ($data) => Events\CollectionCreate::dispatch($data));
    }

    public function update(Request $request)
    {
        return $this->processWebhook($request, fn ($data) => Events\CollectionUpdate::dispatch($data));
    }

    private function processWebhook(Request $request, Closure $eventCallback)
    {
        // Decode data
        $data = json_decode($request->getContent());

        // Dispatch job, passing the resolved store handle (null in single-store mode)
        Jobs\ImportCollectionJob::dispatch($data->id, $request->attributes->get('shopify_store_handle'));

        $eventCallback($data);

        return response()->json([
            'message' => 'Collection has been dispatched to the queue for update',
        ], 200);
    }
}
