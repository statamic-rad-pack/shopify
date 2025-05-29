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

## Creating a Shopify App

To set up a private app on Shopify for this add-on to use, use the following steps:

1. Visit the "Apps" section in your Shopify admin by clicking on the sidebar menu link, then in the modal that appears "App and sales channel settings".
2. Click "Develop Apps" in the top right.
3. Click "Create an app" in the top right to make a new one.
4. Set a nice name to remember as well as your email.
5. Click the "Configuration" Tab.
    1. Click "Configure" next to Admin API Integration.
    2. Enable `read_inventory`, `read_metaobjects`, `read_orders`, `read_products`, `read_product_listings`, `read_publications`, `read_translations` and `write_customers`
    3. Click "Configure" next to Storefront API Integration.
    4. Enable `unauthenticated_read_product_listings`, `unauthenticated_read_product_tags`, `unauthenticated_read_product_inventory`, `unauthenticated_write_customers`, `unauthenticated_write_checkouts`, `unauthenticated_read_customers`, `unauthenticated_read_checkouts`, `unauthenticated_read_metaobjects`
    5. Click "Save" in the top right.
6. Click the "API Credentials" tab. Add the `Admin API access token` to your `.env` as `SHOPIFY_ADMIN_TOKEN`, add `API key` as `SHOPIFY_AUTH_KEY`, add `API secret key` as  `SHOPIFY_AUTH_PASSWORD`, and add `Storefront API access token` as `SHOPIFY_STOREFRONT_TOKEN`.
7. If you've configured the app properly you should see a button that says "Install App". Click this.


## Environment Variables

Ensure you've set the necessary environment variables as defined in the ["Env Values"](/env) section.

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
