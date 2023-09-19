---
title: Scopes
description: ''
category: Frontend
position: 11
---

There are a few scopes included in the addon that help you get the information you need to the frontend.

## VariantIsOnSale

Allows you to filter the Variants Collection to items that are on sale. This is useful if you are creating a sale page.

#### Usage

```twig
{{ collection:variants query_scope="variant_is_on_sale" as="variants" }}

   {{ variants group_by="product_slug" }}
      {{ groups }}
          {{ collection:products :slug:is="product_slug" limit="1" as="product" }}
		      {{ product:title }}
              {{ items }}		
	             {{ sku }} - Price: £{{ price }} (was: £{{ compare_at_price }})
              {{ /items }}
		  {{ /collection:products }}
      {{ /groups }}
   {{ /variants }}

{{ /collection:variants }}
```

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
