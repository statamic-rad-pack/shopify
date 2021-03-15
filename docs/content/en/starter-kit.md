---
title: Starter Kit
category: Installation
position: 5
---

The [starter kit](https://github.com/jackabox/statamic-shopify-starter-kit) is thw recommended way to install Statamic Shopify.

The boilerplate not only installs and sets up the plugin for you but it also comes with a demo that is integrated with Shopify's Storefront API on the front-end. It's built with:

- Tailwind CSS
- ES6 JavaScript

Everything is completely customisable and should give you a running start to your project.

If you are looking to install the add-on to an existing project check out the [getting started](frontend/getting-started) section.


## Install

```bash
git clone git@github.com:jackabox/statamic-shopify-starter-kit.git shopify
cd shopify
rm -rf .git
composer update
cp .env.example .env
php artisan key:generate
```

In short, this will:

- clone the repository
- delete the old `.git` info
- install composer
- copy the .env
- generate a key for the application.

## Create a User

Make a super user to access the control panel.

```bash
php please make:user
```

## Publish the assets

```
yarn && yarn dev
```

## Enjoy

That's it for getting the starter kit setup. For your next steps, you'll need to set up your [Shopify app](setup#creating-a-shopify-app).

Enjoy :)
