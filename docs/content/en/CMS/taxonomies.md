---
title: Taxonomies
category: CMS
position: 6
---

The default taxonomies created by the add-on are:

- `collections` - corresponding to collections in Shopify
- `product_tags` - corresponding to tags in Shopify
- `product_type` - corresponding to type in Shopify
- `vendor` - corresponding to vendor in Shopify

These are fine if you aren't using them on the front-end, however if you want to display products by taxonomify having a URL of `/product_tags/tag-name` is not really great.

## Overriding the Taxonomy Terms
You can override the default tags by the following;

1. Update your taxonomies in the Statamic admin
2. Add the following to your `.env`

```bash
SHOPIFY_TAXONOMY_COLLECTIONS="YOUR TAXONOMY"
SHOPIFY_TAXONOMY_TYPE="YOUR TAXONOMY"
SHOPIFY_TAXONOMY_TAGS="YOUR TAXONOMY"
SHOPIFY_TAXONOMY_VENDOR="YOUR TAXONOMY"
```

If you then re-import/import all of your products the taxonomies will be updated to match.
