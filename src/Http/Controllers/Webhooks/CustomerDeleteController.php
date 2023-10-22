<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use StatamicRadPack\Shopify\Events;

class CustomerDeleteController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        // Decode data
        $data = json_decode($request->getContent());

        if (! is_object($data) && ! $data->id) {
            return;
        }

        Events\CustomerDelete::dispatch($data);

        $customerEntry = User::query()
            ->where('shopify_id', $data->id)
            ->first();

        if ($customerEntry) {
            $customerEntry->remove('shopify_id')->save();
        }

        return response()->json([
            'message' => 'Customer has been deleted',
        ], 200);
    }
}
