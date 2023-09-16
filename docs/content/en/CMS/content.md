---
title: Content
category: CMS
position: 4
---

The addon currently brings in the following content:

- Products.
- Images for products.
- Variants for products.

## Product Content

The addon pulls the following information for products. Although you can remove any of this from the editor there are a few fields that are required for the addon to work.

| Value              | Key                 | Description   | Required  |
| -------------------| ------------------- | ------------- | --------- |
| Product ID         | `product_id`        | Used to fetch data from the admin API. | Y |
| Storefront ID      | `storefront_id`     | Used to integrate the frontend for Shopify | Y |
| Handle             | `slug`              | Identifier for products. Sets as slug | Y |
| Title              | `title`             | Product title as shown in Shopify | |
| Body HTML          | `content`           | Any content from the Shopify editor | |
| Product Image      | `featured_image`    | Main image as set in Shopify | |
| Gallery            | `gallery`           | All further images uploaded to products | |
| Variants           | | All variants, assigned to their own blueprint | |
| Vendor             | `vendor` | Taxonomy for vendor | |
| Tags               | `product_tags` | Taxonomy for product tags | |
| Type               | `product_type` | Taxonomy for product types | |
| Published at       | `published_at` | When the product was published on Shopify | |

<alert type="warning">

By default, we overwrite any content pulled from Shopify over the stored values in Statamic. 

</alert>

You can change this by editing the `overwrite` option in the config file. The following are currently able to be switched between overwriting or not.

```php
/**
  * Whether the importer should overwrite data from Statamic.
  */
'overwrite' => [
    'title' => true,
    'content' => true,
    'vendor' => true,
    'type' => true,
    'tags' => true
]
```

## Variants Content

The addon pulls the following information for variants.

| Value              | Key                 | Description   | Required  |
| -------------------| ------------------- | ------------- | --------- |
| Variant ID         | `slug`              | Used to fetch data from the admin API. | Y |
| SKU                | `sku`               | Unique SKU for product | |
| Product Slug       | `product_slug`      | Used to match the variant to the product | Y |
| Title              | `title`             | Variant title as shown in Shopify | |
| Price              | `price`             | The price for the variant | |
| Inventory Quantity | `inventory_quantity`| How many products are in stock | |
| Weight             | `grams`             | Weight as stored in grams | |
| Option 1           | `option1`           | Option 1 from variant (Default Title if no variants added) | |
| Option 2           | `option2`           | Option 2 from variant (if non, returns null) | |
| Option 3           | `option3`           | Option 3 from variant (if non, returns null) | |
| Requires shipping | `requires_shipping`  | Flag for if the product needs shipping | |
| Image  | `image`  | Variant image as set in Shopify | |


<alert type="warning">

The above fields will always be overwritten to stay in sync with Shopify

</alert>
