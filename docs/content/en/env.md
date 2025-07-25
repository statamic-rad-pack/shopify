---
title: Env Values
category: Installation
position: 3
---

## Required For Admin

| Value             | Description                                                                                                                 |
| -------------------|-----------------------------------------------------------------------------------------------------------------------------|
| `SHOPIFY_APP_URL`  | The url to your store (no https://)                                                                                         |
| `SHOPIFY_ADMIN_TOKEN`  | The Admin API access token you received when generating your app                                                            |
| `SHOPIFY_AUTH_KEY`  | The Admin API key found when creating your private app. This is optional and only required if you want to use the REST API. |
| `SHOPIFY_AUTH_PASSWORD` | The Admin API password found when creating your private app. This is optional and only required if you want to use the REST API.                                                               |

## Required For Frontend

If you are using the JavaScript publishable in the addon you'll need to set the following.

| Value             | Description                                                                                                       |
| -------------------|-------------------------------------------------------------------------------------------------------------------|
| `SHOPIFY_APP_URL`  | The url to your myshopify.com store (no https://)                                                                 |
| `SHOPIFY_STOREFRONT_URL`  | The custom url to your store if you have one (no https://)                                                        |
| `SHOPIFY_STOREFRONT_TOKEN`  | Found in the configuration section of your Headless Sales Channel |

## Required For Webhooks

If you are using the webhook handlers you'll need the following.

| Value             | Description  |
| -------------------| ------------- |
| `SHOPIFY_WEBHOOK_SECRET`  | Found when creating your first notification. Read more about this [here](/CMS/webhooks)  |

## Optional

These allow you to tweak how the system works. Please check out the `config/shopify.php` for a full list of options.

| Value                     | Description                                                                                     |
|---------------------------|-------------------------------------------------------------------------------------------------|
| `SHOPIFY_API_VERSION`     | The version of the storefront and admin APIs you want to target (defaults to '2025-04').        |
| `SHOPIFY_ASSET_CONTAINER` | The asset container you want to use to store assets (defaults to 'shopify')                     |
| `SHOPIFY_ASSET_PATH`      | The path in the container you want to use to store assets (defaults to '').                     |
| `SHOPIFY_COLLECTION_HANDLE` | The handle of the collection that contains your Shopify products, defaults to 'products'.       |
| `SHOPIFY_JOB_QUEUE`         | The queue to run the shopify jobs on. Allows you to set it to a different one than the default. |
| `SHOPIFY_SALES_CHANNEL`     | The Sales Channel to use for product availability, defaults to 'Online Store'.                  |
| `SHOPIFY_TAXONOMY_COLLECTIONS`     | The taxonomy handle to use for shopify collections (defaults to 'collections').                 |
| `SHOPIFY_TAXONOMY_TAGS`     | The taxonomy handle to use for 'tags' of product (defaults to 'tags').                          |
| `SHOPIFY_TAXONOMY_TYPE`     | The taxonomy handle to use for 'types' of product (defaults to 'type').                         |
| `SHOPIFY_TAXONOMY_VENDOR`     | The taxonomy handle to use for 'vendors' of product (defaults to 'vendor').                     |
