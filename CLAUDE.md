# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is **statamic-rad-pack/shopify**, a Statamic addon (Laravel package) that integrates Shopify with Statamic CMS. It uses the Shopify Admin GraphQL API for product imports and webhooks, and the Shopify Storefront API for frontend cart/checkout interactions.

- PHP 8.3/8.4, Laravel 12, Statamic ^6.0
- Namespace: `StatamicRadPack\Shopify`
- Packagist: `statamic-rad-pack/shopify`

## Commands

```bash
# Run all tests
vendor/bin/phpunit -c phpunit.xml

# Run a single test file or filter by name
vendor/bin/phpunit -c phpunit.xml tests/Unit/ImportSingleProductJobTest.php
vendor/bin/phpunit -c phpunit.xml --filter testSomething

# Fix code style
vendor/bin/pint

# Artisan commands (when installed in a host app)
php artisan shopify:import:all           # Import all products via queue
php artisan shopify:import:single        # Import a single product
php artisan shopify:import:collections   # Import all collections
```

## Architecture

### Entry Point

`src/ServiceProvider.php` registers all addon components: Artisan commands, fieldtypes, event listeners, routes, query scopes, CP scripts, and Antlers tags. It also initializes the Shopify API client via `Shopify\Context` and binds `Shopify\Clients\Graphql` into the container (with support for both legacy `admin_token` and OAuth `client_credentials` flow).

### Data Model

Shopify data maps to Statamic content:
- **Products** → Statamic Collection (handle: `products`, configurable via `SHOPIFY_COLLECTION_HANDLE`)
- **Variants** → Statamic Collection (handle: `variants`, always fixed)
- **Tags, Type, Vendor, Collections** → Statamic Taxonomies (handles configurable via `SHOPIFY_TAXONOMY_*` env vars)
- **Images** → Statamic Asset Container (handle: `shopify`)

### Import Pipeline

1. `Commands/ShopifyImportProducts.php` (via `FetchAllProducts` trait) paginates through all Shopify product IDs via GraphQL
2. Each ID is dispatched as `Jobs/ImportSingleProductJob` onto the configured queue
3. `ImportSingleProductJob` fetches full product data (title, content, tags, variants, images, metafields, collections, publication status) and saves/updates Statamic entries
4. The `SavesImagesAndMetafields` trait handles image downloading and metafield parsing (delegated to the class in `config('shopify.metafields_parser')`)
5. In multisite setups, the job also fetches Shopify translations for each locale

### Webhooks

Routes in `routes/actions.php` under `/!/shopify/webhook/...` handle real-time Shopify events. All webhook routes pass through `VerifyShopifyHeaders` middleware (HMAC-SHA256 verification using `SHOPIFY_WEBHOOK_SECRET`). Webhook controllers dispatch `ImportSingleProductJob` for product events, fire Statamic events for others.

### Antlers Tags

`src/Tags/Shopify.php` provides frontend Antlers tags:
- `{{ shopify:variants }}` — fetch variants for current product from the `variants` collection
- `{{ shopify:variants:generate }}` — render built-in variant select form
- `{{ shopify:product_price }}` — display formatted product price
- `{{ shopify:in_stock }}` — check stock status
- `{{ shopify:tokens }}` — output JS config for Storefront API
- `{{ shopify:customer }}` / `{{ shopify:customer_addresses }}` / `{{ shopify:customer_orders }}` — customer data via Admin GraphQL
- `{{ shopify:address_form }}` — address create/update form

### CP Interface

Vue components in `resources/js/` build the Control Panel UI:
- `ImportButton.vue` / `ImportProductButton.vue` — trigger bulk/single imports from the CP dashboard
- `VariantForm.vue` — inline variant editor in the CP
- `Fieldtypes/Variants.vue` / `DisabledText.vue` — custom CP fieldtypes

CP assets are compiled to `dist/` (Vite-built). The `resources/js/shopify/` directory (cart.js, client.js, alpine.js) is publishable to the host app for frontend Storefront API integration.

### Extensibility Points

- **Metafields parser**: swap `config('shopify.metafields_parser')` with a custom class implementing `execute(array $fields, string $context): array`
- **User sync**: `config('shopify.update_shopify_user_job')` for custom user sync job
- **Price formatting**: hookable via `product-price` hook in `Tags/Shopify.php`
- **Overwrite control**: `config('shopify.overwrite')` controls which fields are overwritten on re-import

### Key Config Values

Defined in `config/shopify.php` and set via environment variables:

| Config key | Env var | Purpose |
|---|---|---|
| `url` | `SHOPIFY_APP_URL` | Store `.myshopify.com` URL |
| `client_id` / `client_secret` | `SHOPIFY_CLIENT_ID/SECRET` | OAuth credentials |
| `admin_token` | `SHOPIFY_ADMIN_TOKEN` | Legacy token auth |
| `storefront_token` | `SHOPIFY_STOREFRONT_TOKEN` | Storefront API token |
| `webhook_secret` | `SHOPIFY_WEBHOOK_SECRET` | Webhook HMAC secret |
| `api_version` | `SHOPIFY_API_VERSION` | Defaults to `2025-04` |
| `queue` | `SHOPIFY_JOB_QUEUE` | Queue for import jobs |
| `sales_channel` | `SHOPIFY_SALES_CHANNEL` | Publication channel name (default: `Online Store`) |

### Testing

Tests use `orchestra/testbench` via Statamic's `AddonTestCase`. The `TestCase` sets up product and variant collections/blueprints in memory using `PreventsSavingStacheItemsToDisk`. HTTP calls to Shopify are faked using `Http::fake()`. See `tests/Unit/` for examples.
