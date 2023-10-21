<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Actions;

use Illuminate\Http\Request;
use Shopify\Clients\Rest;
use Statamic\Facades\User;
use Statamic\Support\Arr;

class AddressController extends BaseActionController
{
    public function create(Request $request)
    {
        $customerId = request()->input('customer_id') ?? User::current()?->get('shopify_id') ?? false;

        if (! $customerId) {
            return $this->withErrors($request, __('No customer_id to associate the address with'));
        }

        $validatedData = $request->validate($this->rules());

        try {

            $response = app(Rest::class)->post(path: 'customers/'.$customerId.'/addresses', body: $validatedData);

            if ($response->getStatusCode() == 201) {
                $data = Arr::get($response->getDecodedBody(), 'customer_address', []);

                return $this->withSuccess($request, [
                    'message' => __('Address created'),
                    'address' => $data,
                ]);
            }

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

            $response = app(Rest::class)->delete(path: 'customers/'.$customerId.'/addresses/'.$id);

            if ($response->getStatusCode() == 200) {

                return $this->withSuccess($request, [
                    'message' => __('Address deleted'),
                ]);
            }

        } catch (\Exception $error) {
            return $this->withErrors($request, $error->getMessage());
        }
    }

    public function store(Request $request, $id)
    {
        $customerId = request()->input('customer_id') ?? User::current()?->get('shopify_id') ?? false;

        if (! $customerId) {
            return $this->withErrors($request, __('No customer_id to associate the address with'));
        }

        $validatedData = $request->validate($this->rules());

        try {

            $response = app(Rest::class)->put(path: 'customers/'.$customerId.'/addresses/'.$id, body: $validatedData);

            if ($response->getStatusCode() == 200) {
                $data = Arr::get($response->getDecodedBody(), 'customer_address', []);

                return $this->withSuccess($request, [
                    'message' => __('Address updated'),
                    'address' => $data,
                ]);
            }

        } catch (\Exception $error) {
            dd($error);
            return $this->withErrors($request, $error->getMessage());
        }
    }

    private function rules()
    {
        return [
            'first_name' => ['required', 'string', ],
            'last_name' => ['required', 'string', ],
            'company' => ['nullable', 'string', ],
            'address1' => ['required','string', ],
            'address2' => ['nullable', 'string', ],
            'city' => ['required', 'string', ],
            'province' => ['required', 'string', ],
            'zip' => ['required', 'string', ],
            'phone' => ['nullable', 'string', ],
            'name' => ['nullable', 'string', ],
            'province_code' => ['nullable', 'string', ],
            'name' => ['nullable', 'string', ],
            'country' => ['required', 'string', ],
            'country_code' => ['required_without:country', 'string', 'size:2', ],
            'default' => ['nullable', 'boolean', ],
        ];
    }
}
