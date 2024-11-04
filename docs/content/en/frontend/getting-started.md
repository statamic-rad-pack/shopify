---
title: Getting Started
description: ''
category: Frontend
position: 9
---

As we are pulling all our products into Statamic, we'll want a way to access everything on the front-end from our `antlers` files. There are two options available to you:

1. Storefront API by the [JS Client](https://github.com/Shopify/shopify-app-js/tree/main/packages/api-clients/storefront-api-client#readme)
2. [Buy Buttons](https://www.shopify.co.uk/buy-button) (easiest to set up)

This addon only takes into account the Storefront API as we want more control over what happens on our frontend.

## Demo

To see the front-end in action, you can view a demo at [https://statamic-shopify-demostore.vercel.app](https://statamic-shopify-demostore.vercel.app).

## Storefront API

The [Storefront API](https://github.com/Shopify/shopify-app-js/tree/main/packages/api-clients/storefront-api-client#readme) has a lot of data you can retrieve, however we are only focusing on three elements.

1. Creating a cart instance and remembering it across pages
2. Handling adding products from our CMS to the cart
3. Handling the cart overview page and handing that off to Shopify for checkout.

A more detailed breakdown of our scripts can be found on the [Storefront API](/frontend/storefront-api) page.

If you are looking to spin something up quickly and test things you can follow the example templates step below.

### Example Templates

There are example templates for the `cart`, `product` and `products` page in the addon folder. To get them on the frontend you can run the following command:

```bash
php artisan vendor:publish --tag="shopify-theme"
```

You should also follow the steps on the [Storefront Integration](/frontend/storefront-api). For how to install the custom JavaScript along with what each element does.

<alert type="info">

To easily access your Storefront API Token and site URL from the front end you can use the builtin tag [`shopify_tokens`](/frontend/tags#tokens).

</alert>

You will need to set up the pages in your CP, update your config, and pull down your products before everything will click together.


## Buy Buttons

Buy Buttons work best if you have a small number of products and are looking for less customisation and something more out of the box.

[Buy Buttons](https://www.shopify.co.uk/buy-button) can be set up per product in the Shopify admin, this will generate you some embeddable HTML that you can then paste into a custom field on the admin to output to the frontend.
