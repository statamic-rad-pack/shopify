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
  window.shopifyURL = 'your-site.myshopify.com'
  window.shopifyToken = 'storefront-token'
</script>
```

## Product Price

#### Usage

```twig
{{ shopify:product_price show_from="true" }}
```

- `show_from` will display a "From " prefix to the price if there are multiple variants.

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
{{ shopify:variants:loop }}
    {{ title }}
{{ /shopify:variants:loop }}
```

### From Title

You may only want to pull one variant's data to use, you can do this either from the title.

#### Usage

```twig
{{ shopify:variants:from_title title="Blue" }}
    {{ storefront_id }}
    {{ price }}
{{ /shopify:variants:from_title }}
```

### From Index

You can also pull one variant's data through the index.

#### Usage

```twig
{{ shopify:variants:from_index index="0" }}
    {{ storefront_id }}
    {{ price }}
{{ /shopify:variants:from_index }}
```

## In Stock

Check if a product is in stock or not.

#### Usage

```twig
{{ shopify:in_stock }}
```

```twig
{{ if {shopify:in_stock} }}
{{ /if }}
```
