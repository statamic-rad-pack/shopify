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


### Step 2: Create a private app for Admin API integration
Set up a private app on Shopify for this add-on using the following steps:

1. Visit the "Apps" section in your Shopify admin by clicking on the sidebar menu link, then in the modal that appears "App and sales channel settings".
2. Click "Develop Apps" in the top right.
3. Click "Create an app" in the top right to make a new one.
4. Set a nice name to remember (eg "Statamic Admin") as well as your email.
5. Click the "Configuration" Tab.
    1. Click "Configure" next to Admin API Integration.
    2. Enable `read_inventory`, `read_metaobjects`, `read_orders`, `read_products`, `read_product_listings`, `read_publications`, `read_translations` and `write_customers`
    5. Click "Save" in the top right.
6. Click the "API Credentials" tab. Add the `Admin API access token` to your `.env` as `SHOPIFY_ADMIN_TOKEN`.
7. If you've configured the app properly you should see a button that says "Install App". Click this.

### 3. Shopfront redirection
If you are not intending to use the Shopify storefront you should perform redirection from any Shopify URLs to your website. 

We recommend installing Shopify's [Hydrogen Redirect Theme](https://github.com/Shopify/hydrogen-redirect-theme), and following their setup instructions. 

### 4. Finalise your .env
After completing steps 1 and 2 your .env should look as follows:

```bash
SHOPIFY_APP_URL="your-store.myshopify.com"
SHOPIFY_STOREFRONT_TOKEN="{Public access token}"
SHOPIFY_SALES_CHANNEL="{Sales channel name}"
SHOPIFY_ADMIN_TOKEN="{Admin API access token}"
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
