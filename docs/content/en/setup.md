---
title: Setup
description: ''
position: 2
category: Guide
---

You need to be running Statamic 3 and PHP 7.4 minimum for this plugin to work. It's advisable you also use a queue system that is _not_ sync, for those options you can view the [Laravel docs](https://laravel.com/docs/8.x/queues#driver-prerequisites).

This plugin requires a Shopify account to work. If you're testing this, you can create a free [Shopify Partner](https://www.shopify.co.uk/partners) account. When putting your site live, you'll be required to upgrade to a [Shopify Lite](https://www.shopify.co.uk/lite) account. Third-party apps on the Shopify store may interact with how this product works.

## Installation

Add `jackabox/statamic-shopify` as a dependency to your project:

```bash
composer require jackabox/statamic-shopify
```

## Creating A Shopify App

Before we can get anything working, we need to ensure we have a private app setup in our Shopify Admin. 

1. Visit the "Apps" section in your Shopify admin, the URL should be similar to https://MY-SITE.myshopify.com/admin/apps.
2. Scroll down and click "Manage private apps"
3. Click **"Create new private app"**
4. Set a nice name to remember as well as your email.
5. Toggle **"Show inactive admin api permissions"**
6. Set read access to **"Orders", "Product listings", and "Products"**.
7. Grant access to the Storefront API by checking **"allow this app to access your storefront data using the Storefront API"**.
8. Save.

Once saved you'll be presented with the details of your private app. We'll use these to populate our `.env` file and get everything setup correctly.

## Environment Variables

Ensure you've set the necessary environemnt variables as defined in the ["Env Values"](/env) section.

## Publishable Assets

There are several assets the plugin provides you with

- Config
- Blueprints for Products
- Blueprints for Variants
- Blueprints for Tags, Vendors, and Type Taxomonies
- Asset container for Shopify assets
- Front-end JavaScript to integrate Shopify Storefront API

### Quick Setup

When installing the app for the first time it will copy across all of the necessary assets. If you want to manually do this you can run the following command.

```bash
php artisan vendor:publish --provider="Jackabox\Shopify\ServiceProvider"
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

Copy across the JavaScript files which have been written to speed up your integration with the Storefront API.

```bash
php artisan vendor:publish --tag="shopify-scripts" 
```

#### Theme Files

You can publish the starter theme files if you want to get started quickly or see how the JavaScript integrates.

```bash
php artisan vendor:publish --tag="shopify-theme" 
```
