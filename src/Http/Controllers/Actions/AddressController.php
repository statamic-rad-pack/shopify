<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Actions;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Http\Requests;

class AddressController extends BaseActionController
{
    private const ADDRESS_FIELDS = <<<'FIELDS'
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
        FIELDS;

    public function create(Requests\CreateOrUpdateAddressRequest $request)
    {
        if (! $customerId = $this->customerId()) {
            return $this->withErrors($request, __('No Shopify customer is associated with the current user'));
        }

        $fields = self::ADDRESS_FIELDS;

        try {
            $address = $this->mutate('customerAddressCreate', <<<QUERY
                mutation customerAddressCreate(\$address: MailingAddressInput!, \$customerId: ID!, \$setAsDefault: Boolean) {
                  customerAddressCreate(address: \$address, customerId: \$customerId, setAsDefault: \$setAsDefault) {
                    address {
                      {$fields}
                    }
                    userErrors {
                      field
                      message
                    }
                  }
                }
                QUERY, [
                'address' => $this->addressInput($request->validated()),
                'customerId' => 'gid://shopify/Customer/'.$customerId,
                'setAsDefault' => (bool) $request->validated('default', false),
            ]);

            return $this->withSuccess($request, [
                'message' => __('Address created'),
                'address' => $this->formatAddress($address),
            ]);
        } catch (\Exception $error) {
            return $this->withErrors($request, $error->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        if (! $customerId = $this->customerId()) {
            return $this->withErrors($request, __('No Shopify customer is associated with the current user'));
        }

        try {
            $this->mutate('customerAddressDelete', <<<'QUERY'
                mutation customerAddressDelete($addressId: ID!, $customerId: ID!) {
                  customerAddressDelete(addressId: $addressId, customerId: $customerId) {
                    deletedAddressId
                    userErrors {
                      field
                      message
                    }
                  }
                }
                QUERY, [
                'customerId' => 'gid://shopify/Customer/'.$customerId,
                'addressId' => $this->addressGid($id),
            ], 'deletedAddressId');

            return $this->withSuccess($request, [
                'message' => __('Address deleted'),
            ]);
        } catch (\Exception $error) {
            return $this->withErrors($request, $error->getMessage());
        }
    }

    public function store(Requests\CreateOrUpdateAddressRequest $request, $id)
    {
        if (! $customerId = $this->customerId()) {
            return $this->withErrors($request, __('No Shopify customer is associated with the current user'));
        }

        $fields = self::ADDRESS_FIELDS;

        try {
            $address = $this->mutate('customerAddressUpdate', <<<QUERY
                mutation customerAddressUpdate(\$address: MailingAddressInput!, \$addressId: ID!, \$customerId: ID!, \$setAsDefault: Boolean) {
                  customerAddressUpdate(address: \$address, addressId: \$addressId, customerId: \$customerId, setAsDefault: \$setAsDefault) {
                    address {
                      {$fields}
                    }
                    userErrors {
                      field
                      message
                    }
                  }
                }
                QUERY, [
                'address' => $this->addressInput($request->validated()),
                'addressId' => $this->addressGid($id),
                'customerId' => 'gid://shopify/Customer/'.$customerId,
                'setAsDefault' => (bool) $request->validated('default', false),
            ]);

            return $this->withSuccess($request, [
                'message' => __('Address updated'),
                'address' => $this->formatAddress($address),
            ]);
        } catch (\Exception $error) {
            return $this->withErrors($request, $error->getMessage());
        }
    }

    private function customerId(): mixed
    {
        return User::current()?->get('shopify_id') ?: null;
    }

    /**
     * Run a mutation and return its payload key, throwing on any GraphQL or user error.
     */
    private function mutate(string $operation, string $query, array $variables, string $resultKey = 'address'): mixed
    {
        $response = app(Graphql::class)->query([
            'query' => $query,
            'variables' => $variables,
        ]);

        if ($message = $response->firstError($operation)) {
            throw new \Exception($message);
        }

        if (! $result = Arr::get($response->getDecodedBody(), "data.{$operation}.{$resultKey}")) {
            throw new \Exception(__('Shopify did not return a result'));
        }

        return $result;
    }

    /**
     * MailingAddressInput has no `default` or `name` field; `default` is sent
     * separately as setAsDefault.
     */
    private function addressInput(array $validated): array
    {
        return Arr::except($validated, ['default', 'name']);
    }

    private function addressGid($id): string
    {
        return 'gid://shopify/MailingAddress/'.$id.'?model_name=CustomerAddress';
    }

    private function formatAddress(array $address): array
    {
        $address['id'] = (string) Str::of($address['id'])->afterLast('/')->before('?');

        return $address;
    }
}
