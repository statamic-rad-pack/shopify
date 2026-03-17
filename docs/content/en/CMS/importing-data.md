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

In [multi-store mode](/CMS/multi-store) you can target a specific store with the `--store` option, or omit it to iterate all configured stores:

```bash
php artisan shopify:import:all --store=uk
php artisan shopify:import:all --store=us
php artisan shopify:import:all   # imports from all stores
```

#### Single Product

Fetch a single product by their ID. You can find the ID from the `product_id` value stored on the content.

```bash
php artisan shopify:import:product ID_HERE
```

In multi-store mode you can specify the store the product belongs to:

```bash
php artisan shopify:import:product ID_HERE --store=uk
```

## Import Failures

When an import job fails after all retries are exhausted, two things happen automatically:

1. The error is written to your Laravel log with the product ID and, in multi-store mode, the store handle.
2. A `ProductImportFailed` event is fired, which you can listen for to add your own handling (notifications, alerts, etc.).

```php
use StatamicRadPack\Shopify\Events\ProductImportFailed;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(ProductImportFailed::class, function (ProductImportFailed $event) {
            // $event->productId   — the Shopify product ID
            // $event->storeHandle — the store handle (null in single-store mode)
            // $event->exception   — the Throwable that caused the failure
        });
    }
}
```

## API Rate Limiting

The importer automatically handles Shopify's GraphQL API throttling. After each query, it inspects the `extensions.cost.throttleStatus` returned by Shopify. If the available query budget drops below 500 points, the importer pauses briefly (calculated from Shopify's restore rate) before continuing.

This means large imports will slow down gracefully rather than hitting hard API errors. No configuration is required.

## Image Alt Text

Alt text is imported from Shopify alongside each product and variant image. When an image is first downloaded, its alt text is saved to the Statamic asset's `alt` field. On subsequent imports, if the alt text has changed in Shopify it will be updated on the existing asset.

## Published state

By default the `published` state and `published_at` of the product is determined by the values of `Online Store` sales channel. If you want to use a different sales channel to determine availability you can specify the name of the channel in the `SHOPIFY_SALES_CHANNEL` env variable, e.g. `SHOPIFY_SALES_CHANNEL="My other channel"`.

## Sales channel filtering

By default all Shopify products are imported regardless of whether they are assigned to the configured sales channel. To restrict imports to only products on the sales channel, set `import_all_products` to `false` in `config/shopify.php`:

```php
'import_all_products' => false,
```

When this is enabled, any product that is not assigned to the configured sales channel will be skipped. If a product was previously imported and is later removed from the sales channel, it and all of its variants will be deleted from Statamic on the next import.

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
```

We have provided a helper to convert Shopify's Rich Text meta field type to either HTML or Bard, which you can use as follows:

```php

// html
$html = (new StatamicRadPack\Shopify\Support\RichTextConverter)->convert($metafield['value']);

// bard
$bard = (new StatamicRadPack\Shopify\Support\RichTextConverter)->convert($metafield['value'], true);


```
