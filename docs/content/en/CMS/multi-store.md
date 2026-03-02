---
title: Multi-Store
category: CMS
position: 9
---

The addon supports running **multiple Shopify stores** from a single Statamic installation, or leveraging **Shopify Markets** to serve territory-specific pricing from a single store.

There are three modes:

- **Unified** – all stores share one set of product and variant entries. Store-specific pricing and stock is stored in a `multi_store_data` field on each variant; the primary store's values are also kept at the top level so existing templates continue to work.
- **Localized** – each store maps to a Statamic site. Products and variants are fully independent per site, without Shopify's built-in translation system.
- **Markets** – a single Shopify store serves multiple territories via [Shopify Markets](https://help.shopify.com/en/manual/markets). Territory-specific pricing (via the `contextualPricing` GraphQL API) and inventory is stored in a `market_data` field on each variant, keyed by country code.

## Enabling Multi-Store

Add the following to your `.env`:

```bash
SHOPIFY_MULTI_STORE_ENABLED=true
SHOPIFY_MULTI_STORE_MODE=unified   # or 'localized' or 'markets'
SHOPIFY_MULTI_STORE_PRIMARY=uk     # handle of the store whose data populates top-level fields (unified mode only)
```

Then configure each store in `config/shopify.php` under the `multi_store.stores` key:

```php
'multi_store' => [
    'enabled' => env('SHOPIFY_MULTI_STORE_ENABLED', false),
    'mode' => env('SHOPIFY_MULTI_STORE_MODE', 'unified'),
    'primary_store' => env('SHOPIFY_MULTI_STORE_PRIMARY'),
    'stores' => [
        'uk' => [
            'url' => env('SHOPIFY_STORE_UK_URL'),               // e.g. my-uk-store.myshopify.com
            'storefront_token' => env('SHOPIFY_STORE_UK_STOREFRONT_TOKEN'),
            'webhook_secret' => env('SHOPIFY_STORE_UK_WEBHOOK_SECRET'),
            'client_id' => env('SHOPIFY_STORE_UK_CLIENT_ID'),
            'client_secret' => env('SHOPIFY_STORE_UK_CLIENT_SECRET'),
            'admin_token' => env('SHOPIFY_STORE_UK_ADMIN_TOKEN'),
            'api_version' => env('SHOPIFY_STORE_UK_API_VERSION', '2025-04'),
            'sales_channel' => 'Online Store',
            'currency' => '£',
            'site' => 'en',  // only used in 'localized' mode
        ],
        'us' => [
            'url' => env('SHOPIFY_STORE_US_URL'),
            'storefront_token' => env('SHOPIFY_STORE_US_STOREFRONT_TOKEN'),
            'webhook_secret' => env('SHOPIFY_STORE_US_WEBHOOK_SECRET'),
            'client_id' => env('SHOPIFY_STORE_US_CLIENT_ID'),
            'client_secret' => env('SHOPIFY_STORE_US_CLIENT_SECRET'),
            'admin_token' => env('SHOPIFY_STORE_US_ADMIN_TOKEN'),
            'api_version' => env('SHOPIFY_STORE_US_API_VERSION', '2025-04'),
            'sales_channel' => 'Online Store',
            'currency' => '$',
            'site' => 'fr',  // only used in 'localized' mode
        ],
    ],
],
```

Each store is identified by a short handle (`uk`, `us`, etc.) that you choose. Store handles are used throughout the system as keys.

## Unified Mode

In unified mode a single Statamic entry exists for each product and variant, shared across all stores. Pricing and stock are stored per-store in a `multi_store_data` field on each variant entry:

```yaml
# variant entry data
price: '19.99'              # from the primary store
inventory_quantity: 5       # from the primary store
multi_store_data:
  uk:
    price: '19.99'
    compare_at_price: '24.99'
    inventory_quantity: 5
    inventory_policy: deny
  us:
    price: '24.99'
    compare_at_price: null
    inventory_quantity: 10
    inventory_policy: continue
```

The primary store's values are always written to the top-level fields (`price`, `inventory_quantity`, etc.) so existing templates require no changes when a `store` param is not supplied.

### Enabling the variant blueprint field

After switching on unified multi-store mode, run the following command to add the `multi_store_data` field to the variants blueprint:

```bash
php artisan shopify:multistore:enable
```

The command reads `multi_store.mode` from your config automatically. You can also be explicit:

```bash
php artisan shopify:multistore:enable --mode=unified
```

To remove the field:

```bash
php artisan shopify:multistore:disable
```

## Localized Mode

In localized mode each store maps to a Statamic site. The `site` key in each store's config specifies which Statamic site handle to use. Products and variants are imported into that site's entries independently; no `multi_store_data` field is used.

This mode is an alternative to using Shopify's translation system (see [Multisite](/CMS/multisite)). When importing via a store handle, the built-in Shopify translation-fetching loop is skipped because each store is itself the authoritative source for its locale.

## Markets Mode

Markets mode is for a **single Shopify store** that uses [Shopify Markets](https://help.shopify.com/en/manual/markets) to serve different pricing and availability by territory. No separate store credentials are needed — the existing single-store API connection is used.

### Configuration

Set `mode` to `markets` and list each market country code under `multi_store.markets` in `config/shopify.php`:

```php
'multi_store' => [
    'enabled' => true,
    'mode' => 'markets',
    'markets' => [
        'GB' => ['currency' => '£'],
        'IE' => ['currency' => '€'],
        'US' => ['currency' => '$'],
    ],
],
```

The country codes must match [ISO 3166-1 alpha-2](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2) codes as expected by the Shopify `contextualPricing` GraphQL API.

### Enabling the variant blueprint field

Run the following command to add the `market_data` field to the variants blueprint:

```bash
php artisan shopify:multistore:enable
```

The command reads `multi_store.mode` from your config automatically. You can also be explicit:

```bash
php artisan shopify:multistore:enable --mode=markets
```

To remove the field:

```bash
php artisan shopify:multistore:disable
```

### How data is stored

After importing, each variant entry will have a `market_data` field keyed by country code:

```yaml
# variant entry data
price: '23.00'          # standard Shopify variant price (unchanged)
inventory_quantity: 15  # standard Shopify inventory (unchanged)
market_data:
  GB:
    price: '9.99'
    compare_at_price: null
    inventory_quantity: 10
  IE:
    price: '12.99'
    compare_at_price: null
    inventory_quantity: 5
  US:
    price: '11.99'
    compare_at_price: null
    inventory_quantity: 8
```

The top-level `price` and `inventory_quantity` fields are always written from the standard Shopify variant data and are not affected by markets mode. `market_data` inventory quantities are aggregated across all warehouse locations in that country.

### Frontend tags

Pass the `store` param (using the country code) to any tag to use market-specific data:

```twig
{{ shopify:product_price store="GB" }}
{{ shopify:in_stock store="IE" }}
{{ shopify:tokens store="US" }}

{{ shopify:variants store="GB" }}
    {{ price }} {{# resolves to market_data.GB.price #}}
{{ /shopify:variants }}
```

The `tokens` tag in markets mode outputs the single store's URL and storefront token, with the market's `currency` value from config:

```html
<script>
  window.shopifyConfig = { url: 'your-store.myshopify.com', token: '...', apiVersion: '2025-04', currency: '£' }
</script>
```

## Importing

Use the `--store` option to target a specific store:

```bash
php artisan shopify:import:all --store=uk
php artisan shopify:import:all --store=us
```

Omitting `--store` when multi-store is enabled will iterate across **all** configured stores in sequence:

```bash
php artisan shopify:import:all
```

See [Importing Data](/CMS/importing-data) for full details.

## Webhooks

Shopify always sends an `X-Shopify-Shop-Domain` header with webhook requests. The addon uses this header to:

1. Identify which store the webhook came from.
2. Verify the HMAC signature using **that store's** `webhook_secret`.
3. Pass the resolved store handle to any import jobs dispatched.

Webhook endpoint URLs are the same as in single-store mode. Configure a separate set of webhooks in each Shopify admin pointing to the same Statamic URLs:

```bash
https://YOURSITE/!/shopify/webhook/product/create
https://YOURSITE/!/shopify/webhook/product/update
https://YOURSITE/!/shopify/webhook/product/delete
https://YOURSITE/!/shopify/webhook/collection/create
https://YOURSITE/!/shopify/webhook/collection/update
https://YOURSITE/!/shopify/webhook/order
https://YOURSITE/!/shopify/webhook/customer/create
https://YOURSITE/!/shopify/webhook/customer/update
```

<alert type="warning">
Webhooks from domains not listed in `multi_store.stores` are rejected with a `403` response.
</alert>

## Customers

In multi-store mode a customer may exist with different Shopify IDs across stores. Instead of a single `shopify_id` field, user records store a map:

```yaml
# Statamic user data
shopify_ids:
  uk: 706405506930370084
  us: 819222771040481239
```

The customer webhooks write to `shopify_ids.{storeHandle}` automatically based on the incoming `X-Shopify-Shop-Domain` header. In single-store mode the existing `shopify_id` field is used unchanged.

For the customer tags, pass the `store` param to read the correct ID and use the correct store's API client:

```twig
{{ shopify:customer store="uk" }} ... {{ /shopify:customer }}
{{ shopify:customer:orders store="us" }} ... {{ /shopify:customer:orders }}
```

## Frontend Tags

All tags that display store-specific data accept an optional `store` param in multi-store mode. See [Tags](/frontend/tags) for full details.

### Tokens

Output the storefront credentials for a specific store:

```twig
{{ shopify:tokens store="uk" }}
```

This outputs a `window.shopifyConfig` object including a `currency` key sourced from the store's config, which the bundled Alpine.js integration uses automatically for currency formatting.

### Product Price & Stock

```twig
{{ shopify:product_price store="uk" }}
{{ shopify:in_stock store="us" }}
```

In unified mode these read pricing and stock from `multi_store_data.{store}` on each variant. In markets mode they read from `market_data.{countryCode}`. Without a `store` param the top-level fields are used, so existing templates are unaffected in both modes.

### Variants

```twig
{{# Unified mode #}}
{{ shopify:variants store="uk" }}
    {{ price }} {{# resolves to multi_store_data.uk.price #}}
{{ /shopify:variants }}

{{# Markets mode #}}
{{ shopify:variants store="GB" }}
    {{ price }} {{# resolves to market_data.GB.price #}}
{{ /shopify:variants }}
```

The `price`, `compare_at_price`, and `inventory_quantity` values are automatically overridden from the appropriate store or market data when a `store` param is given.
