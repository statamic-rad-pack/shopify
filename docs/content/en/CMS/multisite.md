---
title: Multisite
category: CMS
position: 8
---

You can make use of Statamic's multi-site features to bring your Shopify translations to your products, variants and collections.

## Setting Up Your API Key

Ensure your API key has enabled the `read_translations` permission for this feature to work.

## Setting up Statamic Collections and Taxonomies

You need to enable multi-site on the `Products` collection and `Collections`, `Product Types`, `Tags` and `Vendor` taxonomies. You can do this by editing the configuration and adding the sites you want to enable to the `sites` field.

On the Products collection ensure that `Propogate` is toggled on, and that the `Origin Behaviour` is set to `Use the site the entry was created in`.

## How it works

Now when your product or collection is updated we will check Shopify for any translations matching the `locale` of your Statamic site. Where they are found, the locale entry in Statamic is updated with those translations.
