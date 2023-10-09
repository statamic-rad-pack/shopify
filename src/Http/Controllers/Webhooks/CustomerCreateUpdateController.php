<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use StatamicRadPack\Shopify\Events;

class CustomerCreateUpdateController extends WebhooksController
{
    public function create(Request $request)
    {
        return $this->processWebhook($request, fn($data) => Events\CustomerCreate::dispatch($data));
    }

    public function update(Request $request)
    {
        return $this->processWebhook($request, fn($data) => Events\CustomerUpdate::dispatch($data));
    }

    private function processWebhook(Request $request, Closure $eventCallback)
    {
        $hmac_header = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $verified = $this->verify($data, $hmac_header);

        if (! $verified) {
            return response()->json(['error' => true], 403);
        }

        // Decode data
        $data = json_decode($data);

        $customerEntry = User::query()
            ->where('shopify_id', $data->id)
            ->first();

        if (! $customerEntry) {

            $customerEntry = User::query()
                ->where('email', $data->email)
                ->first();

            if (! $customerEntry && config('shopify.create_users_from_shopify', false)) {
                $customerEntry = User::make();
                $customerEntry->email($data->email);

                if (User::blueprint()->hasField('first_name')) {
                    $customerEntry->merge([
                        'first_name' => $data->first_name,
                        'last_name' => $data->last_name,
                    ]);
                } else {
                    $customerEntry->merge([
                        'name' => collect([$data->first_name, $data->last_name])->filter()->join(' '),
                    ]);
                }
            }

            if ($customerEntry) {
                $customerEntry->set('shopify_id', $data->id);
                $customerEntry->save();
            }
        }

        $eventCallback($data);

        return response()->json([
            'message' => 'Customer has been updated',
        ], 200);
    }
}
