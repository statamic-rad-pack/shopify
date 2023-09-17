---
title: Importing Data
category: CMS
position: 3
---

The import jobs have been written to pull in your products and sort out all the data. It's advisable that you use a queue system such as Redis. You can either pull all products or a single product.

## Via the Admin

These methods can be accessed through the CMS by finding the **Shopify** section under the **Tools** heading.

<alert type="info">

  You may need to clear your cache after the import. Use `php please cache:clear` to ensure everything is a-okay.
  
</alert>

#### All Products

Fetch all products by clicking the **Import All** button under the Import Products section.

#### Single Product

Fetch a single product by searching for the product in the select and then clicking the **Import Product** button under the Import Single product section. 

## Via Command Line

<alert type="info">

  You may need to clear your cache after the import. Use `php please cache:clear` to ensure everything is a-okay.

</alert>

#### All Products

Fetches all products and runs them through the updater.

```bash
php artisan shopify:import:all
```

#### Single Product

Fetch a single product by their ID. You can find the ID from the `product_id` value stored on the content.

```bash
php artisan shopify:import:single ID_HERE
```

## Metafields

Any product and variant meta fields will be automatically added to the Statamic entry data, with the same handle as their key in Shopify and using the raw value.

You can specify a custom metafield parser to modify the data returned by Shopify before it is saved. This class is specified by `metafields_parser` in `config/shopify.php`. Your custom parser should have an execute method that expects parameters of an array of metafields, a string for the context (product or product-variant), and it should return an array keyed by the handle of the field you want to save to in Statamic.

```php
<?php

namespace App\Shopify;

class MyMetafieldParser
{
    /**
     * Parse any metafields
     *
     * @return array
     */
    public function execute(array $metafields, string $context)
    {
        // do something with $metafields
        return ['key' => 'value', 'another_key' => 'another_value'];
    }
}

