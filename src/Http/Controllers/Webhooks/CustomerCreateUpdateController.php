<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use StatamicRadPack\Shopify\Activity\ActivityStatus;
use StatamicRadPack\Shopify\Events;
use StatamicRadPack\Shopify\Support\Customers;
use StatamicRadPack\Shopify\Support\StoreConfig;

class CustomerCreateUpdateController extends WebhooksController
{
    public function create(Request $request)
    {
        return $this->processWebhook($request, fn ($data) => Events\CustomerCreate::dispatch($data));
    }

    public function update(Request $request)
    {
        return $this->processWebhook($request, fn ($data) => Events\CustomerUpdate::dispatch($data));
    }

    private function processWebhook(Request $request, Closure $eventCallback)
    {
        // Decode data
        $data = json_decode($request->getContent());

        $storeHandle = $request->attributes->get('shopify_store_handle');
        $isMultiStore = $storeHandle && StoreConfig::isMultiStore();

        // Look up user by store-specific Shopify ID or global Shopify ID
        if ($isMultiStore) {
            $customerEntry = User::query()
                ->where('shopify_ids->'.$storeHandle, $data->id)
                ->first();
        } else {
            $customerEntry = User::query()
                ->where('shopify_id', $data->id)
                ->first();
        }

        if (! $customerEntry) {

            if (! $data->email) {
                $this->recordOutcome($request, ActivityStatus::Skipped, ['message' => 'No email address to match a Statamic user']);

                return response()->json([
                    'message' => 'Customer cannot be created as no email was provided',
                ], 200);
            }

            $customerEntry = User::query()
                ->where('email', $data->email)
                ->first();

            $outcome = $customerEntry ? 'Linked to an existing Statamic user' : 'No matching Statamic user';

            if (! $customerEntry && config('shopify.create_users_from_shopify', false)) {
                $outcome = 'Created a Statamic user';

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

                if ($roles = config('statamic.users.new_user_roles')) {
                    $customerEntry->explicitRoles($roles);
                }

                if ($groups = config('statamic.users.new_user_groups')) {
                    $customerEntry->groups($groups);
                }
            }

            if ($customerEntry) {
                Customers::setId($customerEntry, $isMultiStore ? $storeHandle : null, $data->id)->save();
            }

            $this->recordOutcome($request, $customerEntry ? ActivityStatus::Updated : ActivityStatus::Skipped, ['message' => $outcome]);
        } else {
            $this->recordOutcome($request, ActivityStatus::Skipped, ['message' => 'Already linked to a Statamic user']);
        }

        $eventCallback($data);

        return response()->json([
            'message' => 'Customer has been updated',
        ], 200);
    }
}
