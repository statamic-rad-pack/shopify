---
title: Scopes
description: ''
category: Frontend
position: 11
---

There are a few scopes included in the addon that help you get the information you need to the frontend.

## VariantByProduct

Allows you to filter the Variants Collection to a specific Product slug. Useful for returning the variants onto the single product page.

#### Usage

```twig
{{ collection:variants query_scope="variant_by_product" :product="slug" as="variants" }}

    {{ if total_results > 1 }}

        <select name="ss-product-variant" id="ss-product-variant" class="mb-2 p-2 border">
            {{ variants }}
                <option value="{{ storefront_id }}">{{ title }} - £{{ price }}</option>
            {{ /variants}}
        </select>

    {{ else }}

        {{ variants }}
            <input type="hidden" name="ss-product-variant" id="ss-product-variant" value="{{ storefront_id }}">
        {{ /variants}}

    {{ /if }}

{{ /collection:variants }}
```