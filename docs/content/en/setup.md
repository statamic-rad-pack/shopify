---
title: Setup
description: ''
position: 2
category: Installation
---

You need to be running:

- Statamic 3.0+
- PHP 7.4+

It's advisable you also use a queue system that is _not_ sync, for those options you can view the [Laravel docs](https://laravel.com/docs/8.x/queues#driver-prerequisites).

This plugin requires a Shopify account to work. If you're testing this, you can create a free [Shopify Partner](https://www.shopify.co.uk/partners) account. When putting your site live, you'll be required to upgrade to a [Shopify Lite](https://www.shopify.co.uk/lite) account. Third-party apps on the Shopify store may interact with how this product works.

## Installation

Add `statamic-rad-pack/shopify` as a dependency to your project:

```bash
composer require statamic-rad-pack/shopify
```

## Creating a Shopify App (New)

Shopify recently updated the way apps work. The below method is how you should now set up and use the apps.

1. Visit the "Apps" section in your Shopify admin, the URL should be similar to https://MY-SITE.myshopify.com/admin/apps.
2. Click "Develop Apps" in the top right.
3. Click "Create an app" in the top right to make a new one.
4. Set a nice name to remember as well as your email.
5. Click the "Configure" Tab.
    5a. Click "Configure" next to Admin API Integration.
    5b. Set read access to **"Product listings", "Read Inventory", "Read Product Listings" and "Products"**.
    5c. Click "Save" in the top right.
6. Click the "API Credentials" tab. If you've configured you're api integration properly you should see a button that says "Install App". Click this.
7. You'll be presented with an Admin API access token. You can only access this once, so make sure you copy it down and make a note. Add this to your `.env` as `SHOPIFY_ADMIN_TOKEN`

## Creating A Shopify App (Old)

Before we can get anything working, we need to ensure we have a private app setup in our Shopify Admin.

1. Visit the "Apps" section in your Shopify admin, the URL should be similar to https://MY-SITE.myshopify.com/admin/apps.
2. Scroll down and click "Manage private apps"
3. Click **"Create new private app"**
4. Set a nice name to remember as well as your email.
5. Toggle **"Show inactive admin API permissions"**
6. Set read access to **"Orders", "Product listings", and "Products"**.
7. Grant access to the Storefront API by checking **"allow this app to access your storefront data using the Storefront API"**.
8. Save.

Once saved you'll be presented with the details of your private app. We'll use these to populate our `.env` file and get everything set up correctly.

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
