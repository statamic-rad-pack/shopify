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

On the Products collection ensure that `Propagate` is toggled on, and that the `Origin Behaviour` is set to `Use the site the entry was created in`.

## How it works

Now when your product or collection is updated we will check Shopify for any translations matching the `locale` of your Statamic site. Where they are found, the locale entry in Statamic is updated with those translations.

## Multisite vs. Multi-Store

This page describes the **translation-based multisite** approach: one Shopify store, multiple Statamic sites, with Shopify's built-in translation system providing locale-specific content.

If you are running **separate Shopify stores** per region (e.g. a UK store in GBP and a US store in USD), see [Multi-Store](/CMS/multi-store) instead. Multi-store's **localized mode** is an alternative to the translation-based approach — each store maps to a Statamic site and is itself the authoritative source for its locale, so the translation-fetching loop described above is skipped.
