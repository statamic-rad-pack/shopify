---
title: Upgrading
description: ''
position: 3
category: Installation
---

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



