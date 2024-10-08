---
title: Upgrading
description: ''
position: 3
category: Installation
---

## Upgrading from 4.x to 5.x

Due to the essential migration to the Shopify Storefront Checkout API, any previous Javascript integrations that relied on the supplied Javascript files will no longer continue to work. Code updates will be required. See the [Storefront API](frontend/storefront-api) documentation for details on how to set up your integration.

The output of `{{ shopify:tokens }}` has changed, so that `window.shopifyUrl` is now `window.shopifyConfig.url` and `window.shopifyToken` is now `window.shopifyConfig.token`.


## Upgrading from 3.x to 4.x

Due to the changes to how collections are processed, you will now need to set up webhooks for collection create, collection update, and collection delete. See the [webhooks](CMS/webhooks) documentation for full details.

Product status and publish dates now respect the settings from Shopify. To enable date support ensure your Products collection has `Publish Dates` enabled, with `Past Date Behavior` public, and `Future Date Behavior` hidden.

Your app needs to add the `read_publications` and `read_metaobjects` scopes.

## Upgrading from 2.x to 3.x

Due to the migration to the official Shopify PHP library this update is being treated as a breaking change. 

The following .env variables are now required:

`SHOPIFY_ADMIN_TOKEN`
`SHOPIFY_AUTH_KEY`
`SHOPIFY_AUTH_PASSWORD`
`SHOPIFY_WEBHOOK_SECRET`

If all of these are not defined please follow [instructions in setup](/setup).


## Upgrading from 1.x to 2.x

There are a number of **breaking changes** in the 2.x update:

### Changes to tags

Most tags have been prefixed with shopify (eg `{{ shopify:tokens }}`) and a number of tags have been removed or changed. Please refer to the [tags documentation](frontend/tags) for the list of tags.

An overview of the changes is as follows:

`{{ shopify_tokens }}` is now `{{ shopify:tokens }}`

`{{ product_price }}` is now `{{ shopify:product_price }}`

`{{ product_variants:generate }}` is now `{{ shopify:variants:generate }}`

`{{ product_variants:loop }}` is now `{{ shopify:variants }}`

`{{ product_variants:from_title }}` no longer exists, use `{{ shopify:variants title:is="title_value" }}`

`{{ product_variants:from_index }}` no longer exists, use `{{ {shopify:variants}[index] }}`

`{{ in_stock }}` is now `{{ shopify:in_stock }}`



### Changes to urls

Any action URLs, webhook URLs or publish URLs have been changed from `statamic-shopify` to `shopify` e.g. the Product Create webhook is now `https://YOURSITE/!/shopify/webhook/product/create`
