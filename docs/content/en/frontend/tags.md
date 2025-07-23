---
title: Tags
description: ''
category: Frontend
position: 10
---

There are a few tags included in the addon that help you get the information you need to the frontend.

## Tokens

Allows you to output your Site URL and Storefront Token to the front end and binds them to the window so you can use them in the JS SDK. If you are using a custom storefront url instead of a myshopify.com domain, you can specify that with the `storefront_url` key the `config/shopify.php`.

#### Usage

```twig
{{ shopify:tokens }}
```

#### Output

```html
<script>
  window.shopifyConfig = { url: 'your-site.myshopify.com', token: 'storefront-token' }
</script>
```

## Product Price

#### Usage

```twig
{{ shopify:product_price show_from="true" }}
```

- `show_from`: display a "From " prefix to the price if there are multiple variants.
- `show_out_of_stock`: show an "Out of Stock" message if the product is out of stock. Defaults to `true`. If set to `false`, it will return the price even if the product is out of stock.

#### Output

If the product is out of stock returns `out of stock`. 

If not, returns the lowest price of a product. 

```html
Out of Stock

// or

From £50.00 

// or 

£4.99
```

#### product-price hook

This tag provides a [Hook](https://statamic.dev/extending/hooks) to modify the price and currency. For example, if you wanted to format the price using PHP's NumberFormatter:

```php
\StatamicRadPack\Shopify\Tags\Shopify::hook('product-price', function ($payload, $next) {
    $formatter = new \NumberFormatter(\Statamic\Facades\Site::current()->locale(), \NumberFormatter::CURRENCY);

    $payload->price = $formatter->formatCurrency((float) $payload->price, 'EUR');
    
    return $next($payload);
});
```

## Product Variants

You can interact with the variants in several ways. In the demo theme we output this automatically, but you may want to drill down deeper.

### Generate

If you want a simple way to include the variants a tag has been made to load them in. This will either output a select or hidden input depending on how many variants you have.

#### Usage

```twig
{{ shopify:variants:generate show_price="true" show_out_of_stock="true" class="border" }}
```

This will automatically use the `slug` from the context of the post to fetch the variants.

- `show_price`: optional, will use the currency from the config file.
- `show_out_of_stock`: optional, will use the "Out of Stock" lang from the config file.
- `class`: optional, allows you to pass classes down to the select.

#### Output

<code-group>
  <code-block label="Singular" active>

  ```html
  <input type="hidden" name="ss-product-variant" id="ss-product-variant" value="STOREFRONT_ID" />
  ```

  </code-block>
  <code-block label="Multiple">

  ```html
  <select name="ss-product-variant" id="ss-product-variant" class="ss-variant-select">
    ...
    <option value="STOREFRONT_ID">TITLE - PRICE</option>
    <option value="STOREFRONT_ID">TITLE - PRICE</option>
    ...
  </select>
  ```

  </code-block>
</code-group>

### Loop

If you want a bit more manual control over how to handle the variants, you can use the `loop` method.

#### Usage

```twig
{{ shopify:variants }}
    {{ title }}
{{ /shopify:variants }}
```

You can use [tag conditions](https://statamic.dev/conditions) to filter the variants returned, for example:

```twig
{{ shopify:variants title:is="my_title" }}
    {{ title }}
{{ /shopify:variants }}
```


## In Stock

Check if a product is in stock or not.

#### Usage

```twig
{{ shopify:in_stock }}
```

```twig
{{ if {shopify:in_stock} }}
   ...
{{ /if }}
```

## Customer

Return any Shopify customer data associated with the current logged in user, or the `customer_id` passed as a parameter.

#### Usage

```twig
{{ shopify:customer }} ... {{ /shopify:customer }}
```

```twig
{{ shopify:customer customer_id="my_id" }} ... {{ /shopify:customer }}
```

## Customer Addresses

Return any Shopify addresses associated with the current logged in user, or the `customer_id` passed as a parameter.

#### Usage

```twig
{{ shopify:customer:addresses }} ... {{ /shopify:customer:addresses }}
```

```twig
{{ shopify:customer:addresses customer_id="my_id" }} ... {{ /shopify:customer:addresses }}
```

## Customer Address Form

Creates a form that directs to the appropriate endpoint for processing a customer address. 

#### Usage

```twig
{{ shopify:address_form }} ... {{ /shopify:address_form }}
```

```twig
{{ shopify:address_form customer_id="my_id" }} ... {{ /shopify:address_form }}
```

```twig
{{ shopify:address_form address_id="address_id_to_edit" }} ... {{ /shopify:address_form }}
```

You can optionally specific `redirect` and `error_redirect` params to be taken to a different page on success or error respectively.

Any errors during processing will be available in the `{{ errors }}` variable. 


## Customer Orders

Return any Shopify addresses associated with the current logged in user, or the `customer_id` passed as a parameter.

#### Usage

```twig
{{ shopify:customer:orders }} ... {{ /shopify:customer:orders }}
```

```twig
{{ shopify:customer:orders customer_id="my_id" }} ... {{ /shopify:customer:orders }}
```

This tag also supports pagination:

```twig
{{ shopify:customer:orders paginate="10" }} ... {{ /shopify:customer:orders }}
```
