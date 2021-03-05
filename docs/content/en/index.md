---
title: Introduction
description: ''
position: 1
category: ''
features:
  - No more liquid
  - Import Products, Variants, and Images into Statamic
  - Can be used either flat-file (yay!) or with a database.
  - Keeps stock up to date by enabling the Order Webhook.
  - Keeps products in sync by enabling the Product Delete webhook.
  - ES6 JavaScript written integration to the Storefront API saving you hours.
---


A [Statamic 3](https://statamic.com) addon that allows you to integrate [Shopify](https://shopify.com). World-class ecommerce mixed with the brilliance of Statamic.

## Purpose

Shopify is world-class for ecommerce and it provides one of the best systems for handling orders, products, and users. However, the templating engine is frankly _shit_. This plugin aims to marry the wonderful CMS of Statamic with the ecommerce tools of Shopify.

It utilises the Admin API to fetch products and listen to webhooks, and then allows for customisation on the front using either the Storefront API or the Buy Buttons.

## Features

<list :items="features"></list>
