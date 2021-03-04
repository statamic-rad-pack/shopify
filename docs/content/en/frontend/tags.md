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
{{ shopify:tokens }}
```

#### Output

```html
<script>
  window.shopifyURL = 'your-site.myshopify.com'
  window.shopifyToken = 'storefront-token'
</script>
```

## Scripts

Outputs a script link to the demo frontend JavaScript once published.

#### Usage

```twig
{{ shopify:scripts }}
```

#### Output

```html
<script src="https://SITEURL/vendor/shopify/js/statamic-shopify-front.js" async></script>
```
