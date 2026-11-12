<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Closure;
use Illuminate\Http\Request;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Events;

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
        ActivityLog::queueCollectionImport((int) $data->id, $this->storeHandle($request), Activity::SOURCE_WEBHOOK, $this->activityId($request));

        $eventCallback($data);

        return response()->json([
            'message' => 'Collection has been dispatched to the queue for update',
        ], 200);
    }
}
