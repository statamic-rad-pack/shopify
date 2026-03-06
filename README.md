<!-- statamic:hide -->
<div align="center">
    <a href="#">
        <h1>Statamic Shopify</h1>
    </a>

<p>

[![Latest Stable Version](https://poser.pugx.org/statamic-rad-pack/shopify/v)](//packagist.org/packages/statamic-rad-pack/shopify)
[![Total Downloads](https://poser.pugx.org/statamic-rad-pack/shopify/downloads)](//packagist.org/packages/statamic-rad-pack/shopify)
[![License](https://poser.pugx.org/statamic-rad-pack/shopify/license)](//packagist.org/packages/statamic-rad-pack/shopify)

</p>
</div>

A Statamic addon that integrates Shopify with Statamic CMS. Use Shopify for world-class ecommerce while building your storefront with Antlers.

[Live Demo](https://statamic-shopify-demostore.vercel.app) —
[Documentation](https://statamic-shopify-docs.vercel.app) —
[Issues](https://github.com/statamic-rad-pack/shopify/issues) —
[Discussions](https://github.com/statamic-rad-pack/shopify/discussions)
<!-- /statamic:hide -->

## Requirements

- PHP 8.3 or 8.4
- Laravel 12
- Statamic 6

## Features

- Import products, variants, images, and collections from Shopify into Statamic
- Real-time sync via Shopify webhooks (products, orders, collections, customers)
- Antlers tags for frontend product display, cart, and customer account pages
- Control Panel UI for triggering imports and managing variants
- User sync between Statamic and Shopify
- Metafields support with a swappable parser
- Multisite / localisation support
- Multi-store support (unified, localized, and markets modes)
- Works with flat-file or database-backed Statamic

## Installation

Install via Composer:

```bash
composer require statamic-rad-pack/shopify
```

Publish the config file:

```bash
php artisan vendor:publish --tag=shopify-config
```

Publish the frontend JS assets (optional, for Storefront API cart integration):

```bash
php artisan vendor:publish --tag=shopify-scripts
```

## Documentation

Full documentation on configuration, webhooks, Antlers tags, multi-store setup, and more is available at [statamic-shopify-docs.vercel.app](https://statamic-shopify-docs.vercel.app).

## Issues & Feedback

Please open an issue on [GitHub](https://github.com/statamic-rad-pack/shopify/issues) if you encounter a problem.

To propose a new feature, start a [discussion](https://github.com/statamic-rad-pack/shopify/discussions).
