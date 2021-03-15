---
title: Tags
description: ''
category: Frontend
position: 10
---

There are a few tags included in the addon that help you get the information you need to the frontend.

## Tokens

Allows you to output your Site URL and Storefront Token to the front end and binds them to the window so you can use them in the JS SDK.

#### Usage

```twig
{{ shopify_tokens }}
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
{{ product_price :product="slug" show_from="true" }}
```

- `product` slug is required
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

If you want a simple way to include the variants a tag has been made to load them in. If you need to customise things, I advise using the [query_scope]()

#### Usage

```twig
{{ product_variants :product="slug" show_price="true" class="border" }}
```

- `product` slug is required.
- `show_price` is optional - will use the currency from the config file.
- `class` allows you to pass classes down to the select.

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

## In Stock

Check if a product is in stock or not.

#### Usage

```twig
{{ in_stock :product="slug" }}
```

```twig
{{ if {in_stock :product="slug"} }}
{{ /if }}
```

- `product` slug is required.

#### Output

```json
true // or false
```
