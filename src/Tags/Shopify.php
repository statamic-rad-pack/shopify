<?php

namespace StatamicRadPack\Shopify\Tags;

use Shopify\Clients\Rest;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Tags\Concerns\QueriesConditions;
use Statamic\Tags\Concerns\QueriesOrderBys;
use Statamic\Tags\Tags;

class Shopify extends Tags
{
    use QueriesConditions, QueriesOrderBys;

    /**
     * @return string|array
     */
    public function inStock()
    {
        if (! $this->context->get('slug')) {
            return null;
        }

        $variants = $this->fetchProductVariants($this->context->get('slug'));

        if (! $variants) {
            return null;
        }

        return $this->isInStock($variants);
    }

    /**
     * @return string|array
     */
    public function productPrice()
    {
        if (! $this->context->get('slug')) {
            return null;
        }

        $variants = $this->fetchProductVariants($this->context->get('slug'));

        if (! $variants) {
            return null;
        }

        $html = '';

        // Out of Stock
        if (! $this->isInStock($variants)) {
            return __('shopify::messages.out_of_stock');
        }

        // Lowest Price
        $pricePluck = $variants->pluck('price');

        $price = $pricePluck->sort()->splice(0, 1)[0];

        if ($pricePluck->count() > 1 && $this->params->get('show_from') === true) {
            return __('shopify::messages.display_price_from', ['currency' => config('shopify.currency'), 'price' => $price]);
        }

        return __('shopify::messages.display_price', ['currency' => config('shopify.currency'), 'price' => $price]);
    }

    /**
     * @return string|array
     */
    public function scripts()
    {
        return '<script src="'.url('/vendor/shopify/js/statamic-shopify-front.js').'"></script>';
    }

    /**
     * @return string|array
     */
    public function tokens()
    {
        return "<script>
window.shopifyUrl = '".(config('shopify.storefront_url') ?? config('shopify.url'))."';
window.shopifyToken = '".config('shopify.storefront_token')."';
</script>";
    }

    /**
     * handle any shopify:variants:x, shopify:customer:x tags
     */
    public function wildcard($tag)
    {
        if (str_contains($tag, ':')) {
            $method = false;

            if (Str::before($tag, ':') == 'variants') {
                $method = 'variant'.Str::studly(Str::after($tag, ':'));
            }

            if (Str::before($tag, ':') == 'customer') {
                $method = 'customer'.Str::studly(Str::after($tag, ':'));
            }

            if ($method && method_exists($this, $method)) {
                return $this->{$method}();
            }
        }
    }

    /**
     * Generate the output of the variants automatically.
     * Saves having to manually call the index/options.
     *
     * @return string|array
     */
    private function variantGenerate()
    {
        $variants = $this->fetchProductVariants($this->context->get('slug'));

        if (! $variants) {
            return;
        }

        return view('shopify::fields.variant_form', [
            'params' => [
                'class' => $this->params->get('class'),
                'show_out_of_stock' => $this->params->bool('show_out_of_stock') ?? false,
                'show_price' => $this->params->bool('show_price') ?? false,
            ],
            'variants' => $variants->map(function ($variant) {
                $out_of_stock = false;

                if (isset($variant['inventory_policy']) && isset($variant['inventory_management'])) {
                    if (
                        $variant['inventory_policy'] === 'deny' &&
                        $variant['inventory_management'] === 'shopify' &&
                        $variant['inventory_quantity'] <= 0
                    ) {
                        $out_of_stock = true;
                    }
                }

                $langKey = 'shopify::messages.option_title';
                $langParams = ['title' => $variant['title']];

                if ($this->params->bool('show_price')) {
                    $langKey .= '_price';
                    $langParams['price'] = __('shopify::messages.display_price', ['currency' => config('shopify.currency'), 'price' => $variant['price']]);
                }

                if ($this->params->bool('show_out_of_stock') && $out_of_stock) {
                    $langKey .= '_nostock';
                }

                $variant['__out_of_stock'] = $out_of_stock;
                $variant['__translated_string'] = __($langKey, $langParams);
                return $variant;
            }),
        ]);
    }

    /**
     * Return the collection back so we can use it on the front end.
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function variants()
    {
        return $this->fetchProductVariants($this->context->get('slug'));
    }

    /*
     * @deprecated
     */
    private function variantLoop()
    {
        return 'No longer supported, use {{ shopify:variants }} instead';
    }

    /*
     * @deprecated
     */
    private function variantFromTitle()
    {
        return 'No longer supported, use {{ shopify:variants title:is="title" }} instead';
    }

    /*
     * @deprecated
     */
    private function variantFromIndex()
    {
        return 'No longer supported, use {{ {shopify:variants}[index] }} instead';
    }

    /**
     * Turn a variant into a select option
     *
     * @param  array  $variants
     */
    private function variantSelectOptions($variants): string
    {
        $html = '';

        foreach ($variants as $variant) {
            $title = $variant['title'];
            $out_of_stock = false;

            if (isset($variant['inventory_policy']) && isset($variant['inventory_management'])) {
                if (
                    $variant['inventory_policy'] === 'deny' &&
                    $variant['inventory_management'] === 'shopify' &&
                    $variant['inventory_quantity'] <= 0
                ) {
                    $out_of_stock = true;
                }
            }

            $langKey = 'shopify::messages.option_title';
            $langParams = ['title' => $title];

            if ($this->params->get('show_price')) {
                $langKey .= '_price';
                $langParams['price'] = __('shopify::messages.display_price', ['currency' => config('shopify.currency'), 'price' => $variant['price']]);
            }

            if ($this->params->get('show_out_of_stock') && $out_of_stock) {
                $langKey .= '_nostock';
            }

            $html .= '<option value="'.$variant['storefront_id'].'" data-in-stock="'.($out_of_stock ? 'false' : 'true').'"'.($out_of_stock ? ' disabled' : '').'>'.__($langKey, $langParams).'</option>';
        }

        return $html;
    }

    /**
     * Get product variants for a given product slug
     *
     * @param  string  $productSlug
     */
    protected function fetchProductVariants($productSlug)
    {
        $query = Entry::query()
            ->where('collection', 'variants')
            ->where('product_slug', $productSlug);

        $this->queryConditions($query);
        $this->queryOrderBys($query);

        $entries = $query->get();

        if (! $entries->count()) {
            return null;
        }

        return $entries->map(function ($variant) {
            $values = [];
            $values['id'] = $variant->id();
            $values['slug'] = $variant->slug();

            // Map all variant values to data to ensure we are getting everything.
            foreach ($variant->data() as $key => $value) {
                $values[$key] = $value;
            }

            return $values;
        });
    }

    /**
     * Check if all variants are in stock
     *
     * @param  array  $variants
     */
    protected function isInStock($variants): bool
    {
        $stock = 0;
        $deny = false;

        foreach ($variants as $variant) {
            $stock += $variant['inventory_quantity'];

            if (isset($variant['inventory_policy']) && isset($variant['inventory_management'])) {
                $deny = $variant['inventory_policy'] === 'deny' && $variant['inventory_management'] === 'shopify';
            }
        }

        if ($stock === 0 and $deny) {
            return false;
        }

        return true;
    }

    /**
     * Get the data associated with the customer, or the current user
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function customer()
    {
        $id = $this->params->get('customer_id');
        $user = false;

        if (! $id) {
            $user = User::current();

            if (! $user) {
                return ['not_found' => true];
            }
        }

        if (! $user) {
            $user = User::query()
                ->where('shopify_id', $id)
                ->first();

            if (! $user) {
                return ['not_found' => true];
            }
        }

        $response = app(Rest::class)->get(path: 'customers/'.($user->get('shopify_id') ?? 'none'));

        if ($response->getStatusCode() == 200) {
            $data = Arr::get($response->getDecodedBody(), 'customer', []);
        }

        return array_merge($data, $user?->data()->all() ?? []);
    }

    /**
     * Get the customer addresses
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function customerAddresses()
    {
        $id = $this->params->get('customer_id');
        $user = false;

        if (! $id) {
            $user = User::current();

            if (! $user) {
                return ['not_found' => true];
            }
        }

        if (! $user) {
            $user = User::query()
                ->where('shopify_id', $id)
                ->first();

            if (! $user) {
                return ['not_found' => true];
            }
        }

        $response = app(Rest::class)->get(path: 'customers/'.($user->get('shopify_id') ?? 'none').'/addresses.json');

        if ($response->getStatusCode() == 200) {
            $data = Arr::get($response->getDecodedBody(), 'addresses', []);
        }

        return ['addresses' => $data ?? [], 'addresses_count' => count($data ?? [])];
    }

    /**
     * Get the customer orders
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function customerOrders()
    {
        $id = $this->params->get('customer_id');
        $user = false;

        if (! $id) {
            $user = User::current();

            if (! $user) {
                return ['not_found' => true];
            }
        }

        if (! $user) {
            $user = User::query()
                ->where('shopify_id', $id)
                ->first();

            if (! $user) {
                return ['not_found' => true];
            }
        }

        $status = $this->context->get('status');
        if (! in_array($status, ['any', 'open', 'closed', 'cancelled'])) {
            $status = 'any';
        }

        $response = app(Rest::class)->get(path: 'customers/'.($user->get('shopify_id') ?? 'none').'/orders', query: ['status' => $status]);

        if ($response->getStatusCode() == 200) {
            $data = Arr::get($response->getDecodedBody(), 'customer', []);
        }

        return ['orders' => $data ?? [], 'orders_count' => count($data ?? [])];
    }
}
