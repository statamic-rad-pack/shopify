---
title: Env Values
category: Installation
position: 3
---

## Required For Admin

| Value             | Description  | 
| -------------------| ------------- |
| `SHOPIFY_APP_URL`  | The url to your store (no https://)  |
| `SHOPIFY_AUTH_KEY`  | The Admin API key found when creating your private app |
| `SHOPIFY_AUTH_PASSWORD` | The Admin API password found when creating your private app |

## Required For Frontend

If you are using the JavaScript publishable in the addon you'll need to set the following.

| Value             | Description  | 
| -------------------| ------------- |
| `SHOPIFY_APP_URL`  | The url to your store (no https://)  |
| `SHOPIFY_STOREFRONT_TOKEN`  | Found when enabling the Storefront API whilst creating your private app  |

## Required For Webhooks

If you are using the webhook handlers you'll need the following.

| Value             | Description  | 
| -------------------| ------------- |
| `SHOPIFY_WEBHOOK_SECRET`  | Found when creating your first notifcation. Read more about this [here](/CMS/webhooks)  |

