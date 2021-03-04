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

## JS Buy SDK (in Progress)

To provide more customisation to the front-end we'll want to integrate the [JS Buy SDK](https://shopify.github.io/js-buy-sdk/). 

<alert type="warning">

Please not this is still a work in progress. If you feel you have an improvement for the front-end please open a pull request on the [repository](https://github.com/jackabox/statamic-shopify).

</alert>

### Example Templates

There are example templates for the `cart`, `product` and `products` page in the addon folder. To get them on the frontend you can run the following command:

```bash
php artisan vendor:publish --tag="shopify-theme"
```

To work these highly rely on a sample JavaScript file which was written to work in combo. You can publis this as so:

```bash
php artisan vendor:publish --tag="shopify-scripts"
```

You can then manually include this or you can use the `{{ shopify:script }}` tag in your layout header.

If you want to see the source files, and edit it, you can find the source file at `/resources/js/front.js`.