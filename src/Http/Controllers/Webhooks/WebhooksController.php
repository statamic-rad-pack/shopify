<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Activity\ActivityStatus;

class WebhooksController extends Controller
{
    /**
     * The activity log entry the webhook middleware recorded for this delivery.
     */
    protected function activityId(Request $request): ?string
    {
        return $request->attributes->get('shopify_activity_id');
    }

    protected function storeHandle(Request $request): ?string
    {
        return $request->attributes->get('shopify_store_handle');
    }

    /**
     * Record the outcome of a webhook handled without queueing an import.
     */
    protected function recordOutcome(Request $request, ActivityStatus $status, array $attributes = []): void
    {
        ActivityLog::update($this->activityId($request), ['status' => $status->value] + $attributes);
    }
}
