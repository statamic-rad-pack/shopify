<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use StatamicRadPack\Shopify\Activity\ActivityStatus;
use StatamicRadPack\Shopify\Events;
use StatamicRadPack\Shopify\Jobs\UpdateInventoryJob;

class InventoryLevelUpdateController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        $data = json_decode($request->getContent());

        if (empty($data->inventory_item_id)) {
            $this->recordOutcome($request, ActivityStatus::Skipped, ['message' => 'No inventory item in the webhook']);

            return response()->json([], 200);
        }

        $this->recordOutcome($request, ActivityStatus::Queued, ['resource_id' => (string) $data->inventory_item_id]);

        UpdateInventoryJob::dispatch((int) $data->inventory_item_id, $this->storeHandle($request), $this->activityId($request));

        Events\InventoryLevelUpdate::dispatch($data);

        return response()->json([], 200);
    }
}
