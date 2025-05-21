<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Actions;

use Illuminate\Http\Request;
use Shopify\Clients\Graphql;
use Statamic\Facades\User;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use StatamicRadPack\Shopify\Http\Requests;

class AddressController extends BaseActionController
{
    public function create(Requests\CreateOrUpdateAddressRequest $request)
    {
        $customerId = request()->input('customer_id') ?? User::current()?->get('shopify_id') ?? false;

        if (! $customerId) {
            return $this->withErrors($request, __('No customer_id to associate the address with'));
        }

        $validatedData = $request->validated();

        try {
            $query = <<<'QUERY'
            mutation customerAddressCreate(\$address: MailingAddressInput!, \$customerId: ID!, \$setAsDefault: Boolean) {
              customerAddressCreate(address: \$address, customerId: \$customerId, setAsDefault: \$setAsDefault) {
                address {
                  id
                  firstName
                  lastName
                  company
                  address1
                  address2
                  city
                  province
                  country
                  zip
                  phone
                  name
                }
                userErrors {
                  field
                  message
                }
              }
            }
            QUERY;

            $response = app(Graphql::class)->query([
                'query' => $query,
                'variables' => [
                    'address' => $validatedData,
                    'customerId' => 'gid://shopify/Customer/'.$customerId,
                ],
            ]);

            $body = $response->getDecodedBody();

            if ($message = Arr::get($body, 'data.customerAddressCreate.userErrors.message')) {
                throw new \Exception($message);
            }

            $data = Arr::get($body, 'data.customerAddressCreate.address');
            $data['id'] = Str::of($data['id'])->afterLast('/')->before('?');

            return $this->withSuccess($request, [
                'message' => __('Address created'),
                'address' => $data,
            ]);
        } catch (\Exception $error) {
            return $this->withErrors($request, $error->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        $customerId = request()->input('customer_id') ?? User::current()?->get('shopify_id') ?? false;

        if (! $customerId) {
            return $this->withErrors($request, __('No customer_id to associate the address with'));
        }

        try {
            $query = <<<'QUERY'
            mutation customerAddressDelete($addressId: ID!, $customerId: ID!) {
                customerAddressDelete(addressId: $addressId, customerId: $customerId) {
                    deletedAddressId
                    userErrors {
                        message
                    }
                }
            }
            QUERY;

            $response = app(Graphql::class)->query([
                'query' => $query,
                'variables' => [
                    'customerId' => 'gid://shopify/Customer/'.$customerId,
                    'addressId' => 'gid://shopify/MailingAddress/'.$id.'?model_name=CustomerAddress',
                ],
            ]);

            if ($message = Arr::get($response->getDecodedBody(), 'data.customerAddressDelete.userErrors.message')) {
                throw new \Exception($message);
            }

            return $this->withSuccess($request, [
                'message' => __('Address deleted'),
            ]);
        } catch (\Exception $error) {
            return $this->withErrors($request, $error->getMessage());
        }
    }

    public function store(Requests\CreateOrUpdateAddressRequest $request, $id)
    {
        $customerId = request()->input('customer_id') ?? User::current()?->get('shopify_id') ?? false;

        if (! $customerId) {
            return $this->withErrors($request, __('No customer_id to associate the address with'));
        }

        $validatedData = $request->validated();

        try {
            $query = <<<'QUERY'
            mutation customerAddressUpdate(\$address: MailingAddressInput!, $addressId: ID!, \$customerId: ID!, \$setAsDefault: Boolean) {
              customerAddressUpdate(address: \$address, addressId: \$addressId, customerId: \$customerId, setAsDefault: \$setAsDefault) {
                address {
                  id
                  firstName
                  lastName
                  company
                  address1
                  address2
                  city
                  province
                  country
                  zip
                  phone
                  name
                }
                userErrors {
                  field
                  message
                }
              }
            }
            QUERY;

            $response = app(Graphql::class)->query([
                'query' => $query,
                'variables' => [
                    'address' => $validatedData,
                    'addressId' => 'gid://shopify/MailingAddress/'.$id.'?model_name=CustomerAddress',
                    'customerId' => 'gid://shopify/Customer/'.$customerId,
                ],
            ]);

            $body = $response->getDecodedBody();

            if ($message = Arr::get($body, 'data.customerAddressUpdate.userErrors.message')) {
                throw new \Exception($message);
            }

            $data = Arr::get($body, 'data.customerAddressUpdate.address');
            $data['id'] = Str::of($data['id'])->afterLast('/')->before('?');

            return $this->withSuccess($request, [
                'message' => __('Address updated'),
                'address' => $data,
            ]);
        } catch (\Exception $error) {
            return $this->withErrors($request, $error->getMessage());
        }
    }
}
