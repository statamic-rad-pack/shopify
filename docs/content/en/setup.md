---
title: Setup
description: ''
position: 2
category: Installation
---

You need to be running:

- Statamic 5.0+
- PHP 8.1+

It's advisable you also use a queue system that is _not_ sync, for those options you can view the [Laravel docs](https://laravel.com/docs/8.x/queues#driver-prerequisites).

This plugin requires a Shopify account to work. If you're testing this, you can create a free [Shopify Partner](https://www.shopify.co.uk/partners) account. When putting your site live, you'll be required to upgrade to a [Shopify Lite](https://www.shopify.co.uk/lite) account. Third-party apps on the Shopify store may interact with how this product works.

## Installation

Add `statamic-rad-pack/shopify` as a dependency to your project:

```bash
composer require statamic-rad-pack/shopify
```

If you are on a Windows environment, please set the `SHOPIFY_SESSION_STORAGE_PATH` .env value to a writeable path.


### Step 1: Using Shopify's Headless app for front end Storefront integration

In you are using the front end code to access the Storefront API we recommend you set up [Shopify's Headless](https://apps.shopify.com/headless) on your store. This app lets you create a new sales channel that you can use to determine which of your products are available on your Statamic site.

Once you have installed the app you should create a new sales channel, with a meaningful name (eg "Statamic").

In the Sales Channel Credentials screen you should now be able to copy your keys as follows:

```bash
SHOPIFY_APP_URL="your-store.myshopify.com"
SHOPIFY_STOREFRONT_TOKEN="{Public access token}"
SHOPIFY_SALES_CHANNEL="{Sales channel name}"
```


### Step 2: Create an app to access the Admin API on the Shopify Dev Dashboard and get an access token
Set up an app at https://dev.shopify.com/dashboard/ with the following details:

1. App name: "Statamic" or whatever other name you want to use.
2. Redirect URL: https://www.yourwebsite.com (we don't use OAuth redirects, so only add a considered value for if you need it for another reason)
3. Scopes: At a minimum we need: `write_customers, read_inventory, read_metaobjects, read_orders, read_product_listings, read_products, read_publications, read_translations`, however your site may need others.
4. App URL: your website's URL

Once the app is created, add the `Client ID` to your .env as `SHOPIFY_CLIENT_ID` and the `Client Secret` as `SHOPIFY_CLIENT_SECRET`. Then click "Install" and add it to your Shopify store.

This add-on will automatically negotiate a new Admin API token for you when you make GraphQL calls and handle expirations.

### Step 3. Shopfront redirection
If you are not intending to use the Shopify storefront you should perform redirection from any Shopify URLs to your website. 

We recommend installing Shopify's [Hydrogen Redirect Theme](https://github.com/Shopify/hydrogen-redirect-theme), and following their setup instructions. 

### Step 4. Set up webhooks
In order for the addon to receive updates from Shopify about your products and customers you need to set up webhooks. Full details can be found in  ["Webhooks"](/cms/webhooks) section.

### Step 5. Finalise your .env
After completing steps 1-4 your .env should look as follows:

```bash
SHOPIFY_APP_URL="your-store.myshopify.com"
SHOPIFY_STOREFRONT_TOKEN="{Public access token}"
SHOPIFY_SALES_CHANNEL="{Sales channel name}"
SHOPIFY_CLIENT_ID="{App Client ID}"
SHOPIFY_CLIENT_SECRET="{App Client Secret}"
SHOPIFY_WEBHOOK_SECRET="{Webhook Secret}"
```

You may also wish to add some of the option values defined in the ["Env Values"](/env) section.

## Publishable Assets

There are several assets the plugin provides you with

- Config
- Blueprints for Products
- Blueprints for Variants
- Blueprints for Tags, Vendors, and Type Taxonomies
- Asset container for Shopify assets
- Front-end JavaScript to integrate Shopify Storefront API

### Quick Setup

When installing the app for the first time it will copy across all of the necessary assets. If you want to manually do this you can run the following command.

```bash
php artisan vendor:publish --provider="StatamicRadPack\Shopify\ServiceProvider"
```

### Granular Setup

You can install each asset individually.

#### Blueprints

```bash
php artisan vendor:publish --tag="shopify-blueprints"
```

#### Content

```bash
php artisan vendor:publish --tag="shopify-content"
```

#### Config

```bash
php artisan vendor:publish --tag="shopify-config"
```

#### JavaScript

Publishes the JavaScript files which have been created to speed up your integration with the Storefront API.

```bash
php artisan vendor:publish --tag="shopify-scripts"
```

#### Theme Files

You can publish the starter theme files if you want to get started quickly or see how the JavaScript integrates.

```bash
php artisan vendor:publish --tag="shopify-theme"
```
