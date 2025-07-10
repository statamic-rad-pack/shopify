<?php

namespace StatamicRadPack\Shopify\Tags;

use Shopify\Clients\Graphql;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\Hookable;
use Statamic\Tags\Concerns\GetsRedirects;
use Statamic\Tags\Concerns\OutputsItems;
use Statamic\Tags\Concerns\QueriesConditions;
use Statamic\Tags\Concerns\QueriesOrderBys;
use Statamic\Tags\Concerns\RendersForms;
use Statamic\Tags\Tags;

class Shopify extends Tags
{
    use GetsRedirects, Hookable, OutputsItems, QueriesConditions, QueriesOrderBys, RendersForms;

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

        // Out of Stock
        if (! $this->isInStock($variants)) {
            return __('shopify::messages.out_of_stock');
        }

        // Lowest Price
        $pricePluck = $variants->pluck('price');

        $price = $pricePluck->sort()->first();

        $payload = $this->runHooksWith('product-price', [
            'currency' => config('shopify.currency'),
            'price' => $price,
        ]);

        if ($pricePluck->count() > 1 && $this->params->get('show_from') === true) {
            return __('shopify::messages.display_price_from', ['currency' => $payload->currency, 'price' => $payload->price]);
        }

        return __('shopify::messages.display_price', ['currency' => $payload->currency, 'price' => $payload->price]);
    }

    /**
     * @return string|array
     */
    public function tokens()
    {
        return "<script>
window.shopifyConfig = { url: '".(config('shopify.storefront_url') ?? config('shopify.url'))."', token: '".config('shopify.storefront_token')."', apiVersion: '".(config('shopify.api_version')."' };
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

                if (isset($variant['inventory_policy'])) {
                    if (
                        $variant['inventory_policy'] === 'deny' &&
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

            if (isset($variant['inventory_policy'])) {
                if (
                    $variant['inventory_policy'] === 'deny' &&
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

            $html .= '<option value="'.$variant['id'].'" data-in-stock="'.($out_of_stock ? 'false' : 'true').'"'.($out_of_stock ? ' disabled' : '').'>'.__($langKey, $langParams).'</option>';
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

            if (isset($variant['inventory_policy'])) {
                $deny = $variant['inventory_policy'] === 'deny';
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
    public function addressForm()
    {
        $endpoint = route('statamic.shopify.address.create');

        $id = $this->params->get('address_id');
        if ($id) {
            $endpoint = route('statamic.shopify.address.store', ['id' => $id]);
        }

        $knownParams = ['redirect', 'error_redirect', 'address_id', 'customer_id'];

        $html = $this->formOpen($endpoint, 'POST', $knownParams);

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        if (! $this->parser) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $id = $this->params->get('customer_id');

        if (! $id) {
            if ($user = User::current()) {
                $id = $user->get('shopify_id');
            }
        }

        if ($id) {
            $html .= '<input type="hidden" name="customer_id" value="'.$id.'" />';
        }

        $html .= $this->formMetaFields($params);
        $html .= $this->parse();
        $html .= $this->formClose();

        return $html;
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

        $customerId = $user->get('shopify_id') ?? 'none';

        $query = <<<QUERY
            {
              customer(id: "gid://shopify/Customer/{$customerId}") {
                email
                displayName
                note
                lastOrder {
                    id
                }
              }
            }
            QUERY;

        $response = app(Graphql::class)->query(['query' => $query]);

        if ($data = Arr::get($response->getDecodedBody() ?? [], 'data.customer', [])) {
            $data = [
                'email' => $data['email'],
                'name' => $data['displayName'],
                'last_order_id' => Arr::get($data, 'lastOrder.id'),
                'note' => $data['note'],
            ];
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
                return ['addresses' => [], 'addresses_count' => 0];
            }
        }

        if (! $user) {
            $user = User::query()
                ->where('shopify_id', $id)
                ->first();

            if (! $user) {
                return ['addresses' => [], 'addresses_count' => 0];
            }
        }

        $customerId = $user->get('shopify_id') ?? 'none';

        $query = <<<QUERY
            {
              customer(id: "gid://shopify/Customer/{$customerId}") {
                addresses {
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
              }
            }
            QUERY;

        $response = app(Graphql::class)->query(['query' => $query]);

        if ($data = Arr::get($response->getDecodedBody() ?? [], 'data.customer.addresses', [])) {
            $data = collect($data)
                ->map(function ($address) {
                    $address['id'] = Str::of($address['id'])->afterLast('/')->before('?');

                    return $address;
                })
                ->values()
                ->all();
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
                return ['orders' => [], 'orders_count' => 0];
            }
        }

        if (! $user) {
            $user = User::query()
                ->where('shopify_id', $id)
                ->first();

            if (! $user) {
                return ['orders' => [], 'orders_count' => 0];
            }
        }

        $status = '';
        if (! in_array($this->context->get('status'), ['not_closed', 'open', 'closed', 'cancelled'])) {
            $status = ' AND status = '.$this->context->get('status');
        }

        $userId = ($user->get('shopify_id') ?? 'none');

        $query = <<<QUERY
            query (\$numItems: Int!, \$cursor: String) {
              orders(first: \$numItems, after: \$cursor, query: "customer_id:$userId $status") {
                nodes {
                  id
                  billingAddress {
                    address1
                    address2
                    name
                    company
                    city
                    country
                    phone
                    province
                    zip
                  }
                  shippingAddress {
                    address1
                    address2
                    name
                    company
                    city
                    country
                    phone
                    province
                    zip
                  }
                  lineItems(first: 250) {
                    nodes {
                      id
                      quantity
                      sku
                      title
                      image {
                        url
                      }
                      discountedUnitPriceSet {
                        presentmentMoney {
                          currencyCode
                          amount
                        }
                      }
                      originalUnitPriceSet {
                        presentmentMoney {
                          currencyCode
                          amount
                        }
                      }
                    }
                  }
                  totalPriceSet {
                    presentmentMoney {
                      currencyCode
                      amount
                    }
                  }
                  totalReceivedSet {
                    presentmentMoney {
                      currencyCode
                      amount
                    }
                  }
                  totalDiscountsSet {
                    presentmentMoney {
                      currencyCode
                      amount
                    }
                  }
                  requiresShipping
                  cancelledAt
                  cancelReason
                  createdAt
                  discountCodes
                }
                pageInfo {
                  hasNextPage
                  endCursor
                }
              }
            }
            QUERY;

        $data = [];
        $items = [];

        do {
            $response = app(Graphql::class)->query([
                'query' => $query,
                'variables' => [
                    'numItems' => 100,
                    'cursor' => Arr::get($data, 'data.orders.pageInfo.endCursor', null),
                ],
            ]);

            $data = $response->getDecodedBody();

            if ($orders = Arr::get($data, 'data.orders.nodes', [])) {
                $items = array_merge($items, collect($orders)->map(function ($order) {
                    $order['id'] = (int) Str::afterLast($order['id'], '/');

                    Arr::set(
                        $order,
                        'lineItems.nodes',
                        collect(Arr::get($order, 'lineItems.nodes'))
                            ->map(function ($lineItem) {
                                $lineItem['id'] = (int) Str::afterLast($lineItem['id'], '/');

                                return $lineItem;
                            })->all()
                    );

                    return $order;
                })->all());
            }

        } while (Arr::get($data, 'data.orders.pageInfo.hasNextPage', false));

        if (! $this->params->get('as')) {
            $this->params->put('as', 'orders');
        }

        $data = collect($items ?? []);

        if ($paginate = $this->params->int('paginate')) {
            $data = new LengthAwarePaginator(
                $data,
                count($data),
                $paginate
            );
        }

        return array_merge($this->output($data), ['orders_count' => count($data ?? [])]);
    }

    protected function paginatedOutput($paginator)
    {
        $paginator->withQueryString();

        if ($window = $this->params->int('on_each_side')) {
            $paginator->onEachSide($window);
        }

        $as = $this->getPaginationResultsKey();
        $items = $paginator->getCollection()->map(function ($item) use ($paginator) {
            $item['total_results'] = $paginator->total();

            return $item;
        });

        return array_merge([
            $as => $items,
            'paginate' => $this->getPaginationData($paginator),
        ], $this->extraOutput($items));
    }
}
