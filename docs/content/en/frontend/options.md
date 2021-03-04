---
title: Options
description: ''
category: Frontend
position: 9
---

You have two options when using Shopify on the front-end. 

1. [Buy Buttons](https://www.shopify.co.uk/buy-button) (easiest to setup)
2. Storefront API by the [JS SDK](https://shopify.github.io/js-buy-sdk/)

## Buy Buttons

This works best if you have a small amount of products and are looking to get off the ground quicker.

Buy buttons can be setup per product in the Shopify admin and then you can add this to each individual product in your CMS. This will implement all the logic you need for checking variations, product quantity, and handling the cart.

## JS Buy SDK

To provide more customisation to the front-end we'll want to integrate the [JS Buy SDK](https://shopify.github.io/js-buy-sdk/). You can view more about this on the [JS SDK](/frontend/js-sdk) page. If you want to see the basic theme set up in the addon you can publish the 

### Example Templates

There are example templates for the `cart`, `product` and `products` page in the addon folder. To get them on the frontend you can run the following command:

```bash
php artisan vendor:publish --tag="shopify-theme"
```

You can also include the precompiled scripts with the following.

```bash
php artisan vendor:publish --tag="shopify-include-scripts"
```

If you update your layour with the tags it should pull everything together.

```twig
{{ shopify_tokens }}
{{ shopify_scripts }}
```

You will need to setup the pages in your CP, update your config, and pull down your products before everything will click together.
