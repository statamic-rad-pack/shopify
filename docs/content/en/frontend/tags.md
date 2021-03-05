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

## Product Variants

If you want a simple way to include the variants a tag has been made to load them in. If you need to customise things, I advise using the [query_scope]()

#### Usage

```twig
{{ product_variants :product="slug" currency="£" }}
```

- Product is required
- Currency is optional.

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

