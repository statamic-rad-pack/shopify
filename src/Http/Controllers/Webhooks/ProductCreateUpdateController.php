<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Closure;
use Illuminate\Http\Request;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Events;

class ProductCreateUpdateController extends WebhooksController
{
    public function create(Request $request)
    {
        return $this->processWebhook($request, fn ($data) => Events\ProductCreate::dispatch($data));
    }

    public function update(Request $request)
    {
        return $this->processWebhook($request, fn ($data) => Events\ProductUpdate::dispatch($data));
    }

    private function processWebhook(Request $request, Closure $eventCallback)
    {
        $data = json_decode($request->getContent());

        // Queue the import against this delivery's activity, passing the resolved store handle (null in single-store mode)
        ActivityLog::queueProductImport((int) $data->id, $this->storeHandle($request), Activity::SOURCE_WEBHOOK, $this->activityId($request));

        $eventCallback($data);

        return response()->json([
            'message' => 'Product has been dispatched to the queue for update',
        ], 200);
    }
}
