<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use StatamicRadPack\Shopify\Events;
use StatamicRadPack\Shopify\Support\StoreConfig;

class CustomerDeleteController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        // Decode data
        $data = json_decode($request->getContent());

        if (! is_object($data) || empty($data->id)) {
            return;
        }

        Events\CustomerDelete::dispatch($data);

        $storeHandle = $request->attributes->get('shopify_store_handle');

        if ($storeHandle && StoreConfig::isMultiStore()) {
            $customerEntry = User::query()
                ->where('shopify_ids->'.$storeHandle, $data->id)
                ->first();

            if ($customerEntry) {
                $shopifyIds = $customerEntry->get('shopify_ids', []);
                unset($shopifyIds[$storeHandle]);

                $shopifyIds
                    ? $customerEntry->set('shopify_ids', $shopifyIds)
                    : $customerEntry->remove('shopify_ids');

                $customerEntry->save();
            }
        } elseif ($customerEntry = User::query()->where('shopify_id', $data->id)->first()) {
            $customerEntry->remove('shopify_id')->save();
        }

        return response()->json([
            'message' => 'Customer has been deleted',
        ], 200);
    }
}
