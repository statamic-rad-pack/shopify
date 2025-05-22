<?php

namespace StatamicRadPack\Shopify\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Traits\Localizable;
use Statamic\Facades\Site;

class CreateOrUpdateAddressRequest extends FormRequest
{
    use Localizable;

    public function rules(): array
    {
        return [
            'firstName' => ['required', 'string'],
            'lastName' => ['required', 'string'],
            'company' => ['nullable', 'string'],
            'address1' => ['required', 'string'],
            'address2' => ['nullable', 'string'],
            'city' => ['required', 'string'],
            'province' => ['required', 'string'],
            'zip' => ['required', 'string'],
            'phone' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'provinceCode' => ['nullable', 'string'],
            'country' => ['required', 'string'],
            'countryCode' => ['required_without:country', 'string', 'size:2'],
            'default' => ['nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function validateResolved()
    {
        $site = Site::findByUrl(URL::previous()) ?? Site::default();

        return $this->withLocale($site->lang(), fn () => parent::validateResolved());
    }
}
