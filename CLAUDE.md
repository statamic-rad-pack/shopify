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
php artisan shopify:webhooks:register    # Register all webhook subscriptions in Shopify
php artisan shopify:webhooks:register --store=uk  # Register for a specific store (multi-store)
```

## Architecture

### Entry Point

`src/ServiceProvider.php` registers all addon components: Artisan commands, fieldtypes, event listeners, routes, query scopes, CP scripts, and Antlers tags. It also initializes the Shopify API client via `Shopify\Context` and binds `Shopify\Clients\Graphql` into the container (with support for both legacy `admin_token` and OAuth `client_credentials` flow).

### Enums

`src/Enums/WebhookTopic.php` — backed enum listing every supported Shopify webhook topic (the register command, status card and activity filters all read it; tests count `cases()` rather than a number). Each case exposes `->routeName()` and `->callbackUrl()` so the topic→route mapping is defined in one place and shared by the register command and the CP status controller.

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
4. The `SavesImagesAndMetafields` trait handles image downloading and metafield parsing (delegated to the class in `config('shopify.metafields_parser')`). Alt text from Shopify is saved/updated on the Statamic asset's `alt` field.
5. In multisite setups, the job also fetches Shopify translations for each locale
6. All GraphQL calls in the import pipeline go through `ThrottlesShopifyRequests::queryWithThrottle()`, which inspects `extensions.cost.throttleStatus` and sleeps if the available query budget drops below 500 points.
7. `ImportSingleProductJob` uses Statamic's `Hookable` trait (same mechanism as the `product-price` tag hook) to expose two hooks: `import-product-query` (modify the GraphQL query before it's sent) and `import-product-data` (modify the data array before it's merged onto the entry). See `docs/content/en/CMS/importing-data.md`.

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
- `WebhookStatus.vue` — fetches and displays registered Shopify webhooks with status badges (uses Statamic UI library: `ui-table`, `ui-badge`, `ui-error-message`)
- `VariantForm.vue` — inline variant editor in the CP
- `Fieldtypes/Variants.vue` / `DisabledText.vue` — custom CP fieldtypes

The CP dashboard (`resources/views/cp/dashboard.blade.php`) has a **Webhook Status** card that renders `WebhookStatus.vue`, backed by `Http\Controllers\CP\WebhooksStatusController` at `GET /cp/shopify/webhooks/status`.

CP assets are compiled to `dist/` (Vite-built). The `resources/js/shopify/` directory (cart.js, client.js, alpine.js) is publishable to the host app for frontend Storefront API integration.

### Traits

- `FetchAllProducts` / `FetchCollections` — paginate through Shopify product/collection IDs and dispatch import jobs. Both use `ThrottlesShopifyRequests`.
- `SavesImagesAndMetafields` — downloads images to Statamic assets (with alt text), parses metafields via the configured parser.
- `StoreConfig::clientFor(?string $handle)` — the one way to get a Graphql client: the store's client in multi-store mode, else the default. Use `StoreConfig::isUnifiedMode()` / `isLocalizedMode()` / `isMarketsMode()` rather than comparing `getMode()`.
- `WebhookSubscriptions` (`src/Support`) — fetches registered webhook subscriptions and reads/writes the "last received" cache keys (shared by the register command, status controller and webhook middleware).
- `ImportSingleProductJob` uses `WithoutOverlapping` keyed by product + store, so several queue workers are safe. `VerifyShopifyHeaders` skips duplicate deliveries by `X-Shopify-Webhook-Id` (only after a successful response).
- Activity log (`src/Activity`): `ActivityRepository` contract (`src/Contracts`) with `FileActivityRepository` (default) and `DatabaseActivityRepository` (`shopify.activity.driver`; migration in `database/migrations`, loaded only for the database driver). Always go through `ActivityLog` (swallows storage failures; no-op when disabled). Queue imports with `ActivityLog::queueProductImport()` / `queueCollectionImport()` so they're logged; jobs take an optional `$activityId` and record their outcome. `VerifyShopifyHeaders` records each delivery (received / duplicate / rejected) and puts its id on the request as `shopify_activity_id`; webhook controllers use `WebhooksController::recordOutcome()`. CP: `ActivityController`, `pages/Activity.vue`, `components/ActivityLog.vue`. Tests write the log to the fake stache folder.
- Product blueprint (`resources/blueprints/product.yaml`): imported, read-only data lives in a "Details" tab (handle `shopify`) with "Organization" (vendor, type, tags), "Pricing and inventory" (`in_stock`, `on_sale`, `price_min`, `price_max`) and "Shopify record" (product_id, published_at, `shopify_synced_at` shown as "Last imported") sections; the sidebar only has the slug. `UpdateScripts\AddSyncFieldsToProductBlueprint` adds just the new fields (`FIELDS`) a blueprint is missing, in their sections (read from that YAML), and never moves existing fields. `shopify_synced_at` is stored as `Y-m-d H:i:s` to match the date field.
- Prices and currency: `Support\Money` resolves the currency symbol/code per store or market (`currency`, `currency_code`) and formats with `NumberFormatter` when `money_format` is `intl`; `formatPrice()` in the tags and `tokens()` use it. The bundled JS mirrors this in `resources/js/shopify/money.js` (`formatMoney`, `cartStorageKey`).
- Cart JS (`resources/js/shopify/cart.js`): all operations go through `run()` (GraphQL variables, `@inContext(country:)` when `shopifyConfig.country` is set, `shopify.cart.error` event on failure, returns `undefined`). Keep exported names/signatures stable: sites publish these files. JS tests: `npm run test:js` (node:test) and `npm run test:js:live` (real Storefront API via `.env.live`).
- Images (`SavesImagesAndMetafields::importImages($image, $mediaId, $previousPaths)`): assets store `shopify_media_id` and `shopify_image_version` (the CDN URL's `?v=`). Same file name with a new version → `Asset::reupload()` (via `Support\DownloadedReplacementFile`, which reads the download from the local disk); same media ID under a new file name, found among the entry's previous image paths → new asset + `Asset::replace()` (Statamic updates references; `shopify.asset.delete_replaced`). Assets without a stored version just record it. `$updatedImages` feeds `images_updated` in the activity log changes. `Listeners\AddShopifyFieldsToAssetBlueprint` adds a read-only `shopify_media_id` field to the Shopify container's asset blueprint at runtime (`AssetContainerBlueprintFound`), hidden by an `if` when empty; the base blueprint is shared per container, so it is never added per asset.
- Order webhooks: `WebhooksController::queueLineItemImports()` groups line items per product and queues `ImportSingleProductJob` with `orderData` quantities; orders add (+1), `RefundCreateController` and `OrderCancelledController` subtract (-1) on the refund / cancellation date. A cancellation subtracts only what the refunds in its payload don't cover, since Shopify also sends `refunds/create` for those. Negative quantities never update `last_purchased`.
- `inventory_levels/update` → `Jobs\UpdateInventoryJob`: looks up the inventory item's variant (total `inventoryQuantity`) and updates that variant entry + `ProductSummary::syncForVariant()`; in multi-store mode it re-imports the product for the store instead.
- `ProductSummary` (`src/Support`) — derives `price_min`, `price_max`, `in_stock`, `on_sale` from a product's variants. Written onto the product on import, on CP variant edit (`syncForVariant`), and backfilled by `UpdateScripts\AddProductSummary`. Used by the `product_has_stock` scope and, when a product is in context, the `product_price` / `in_stock` tags.
- `ThrottlesShopifyRequests` — wraps `Graphql::query()` calls; inspects `extensions.cost.throttleStatus` and calls `throttleSleep()` if available budget < `config('shopify.throttle_threshold')` points. Retries `THROTTLED` responses, throws `Exceptions\GraphqlException` when a query fails outright (no `data`), and only logs a warning for partial errors (e.g. a missing access scope on one field). The sleep is extracted into a protected method so tests can override it without actually sleeping.
- Mutation error handling: use `HttpResponse::firstError('mutationName')`, which returns the first top-level `errors` message or the first `data.{mutation}.userErrors` message (`userErrors` is a list).

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
| `api_version` | `SHOPIFY_API_VERSION` | Defaults to `Graphql::DEFAULT_API_VERSION` (`2026-01`) |
| `api_max_retries` | `SHOPIFY_API_MAX_RETRIES` | Library retries for 429 / 5xx responses (default 2) |
| `throttle_threshold` | `SHOPIFY_THROTTLE_THRESHOLD` | Query cost budget below which imports pause (default 500) |
| `queue` | `SHOPIFY_JOB_QUEUE` | Queue for import jobs |
| `sales_channel` | `SHOPIFY_SALES_CHANNEL` | Publication channel name (default: `Online Store`) |

### Testing

Tests use `orchestra/testbench` via Statamic's `AddonTestCase`. The `TestCase` sets up product and variant collections/blueprints in memory using `PreventsSavingStacheItemsToDisk`. HTTP calls to Shopify are faked using `Http::fake()`. See `tests/Unit/` for examples.
