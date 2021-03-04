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

Before we can add the `.env` variables, we need to ensure we have a private app setup in our Shopify Admin. 

1. Visit the "Apps" section in your Shopify admin, the URL should be similar to https://MY-SITE.myshopify.com/admin/apps.
2. Scroll down and click "Manage private apps"
3. Click "Create new private app"
4. Set a nice name to remember as well as your email.
5. Toggle "Show inactive admin api permissions"
6. Set read access to "Orders", "Product listings", and "Products"
7. If you plan to use the JS SDK check "allow this app to access your storefront data using the Storefront API" with the default settings.
8. Save.

Once saved you'll be presented with the details of your private app. We'll use these to populate our `.env` file and get everything setup correctly.

## Environment Variables

Ensure you've set the following variables. Some may mitigated if you only require CMS integration.

| Env Value                 | Shopify App Value        | Required For  |
| ------------------------- | ------------------------ | ------------- |
| SHOPIFY_APP_URL           | your-site.myshopify.com  | Frontend, CMS |
| SHOPIFY_AUTH_KEY          | Admin API Key            | CMS |
| SHOPIFY_AUTH_PASSWORD     | Admin API Password       | CMS |
| SHOPIFY_STOREFRONT_TOKEN  | Storefront access token  | Frontend |


## Publishable Assets

There are several assets the plugin provides you with

- Config
- Blueprints for Products
- Blueprints for Variants
- Blueprints for Tags, Vendors, and Type Taxomonies
- Asset container for Shopify assets
- Front-end JS SDK setup script

### Quick Setup

To copy the blueprints, configs, javascript(s), and theme files you can publish everything. I'd advise using the granular approach to set only the things you need up.

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

#### Modular Scripts

Best if you are going to customise everything.

```bash
php artisan vendor:publish --tag="shopify-modular-scripts" 
```

#### Theme Files

You can publish the starter theme files if you want to get started quickly or see how the JavaScript integrates.

```bash
php artisan vendor:publish --tag="shopify-theme" 
```

#### Compiled Scripts

If you want a quick set up, use the compiled scripts.

```bash
php artisan vendor:publish --tag="shopify-include-scripts" 
```
