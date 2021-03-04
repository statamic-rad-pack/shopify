---
title: Webhooks
category: CMS
position: 7
---

There is a built method to listen for webhooks sent by Shopify back to your application. These are validated based on a secret you received when setting them up.

## Setting Up Webhooks

1. Head to your Shopify admin
2. Click **Settings > Notifcations**
3. Scroll down to **Webhooks** and click "Create Webhook"
4. Enter the URL as shown in the sections below.

<alert type="info">

If you are trying to test any webhooks locally you'll need to use a service like [Ngrok](https://ngrok.com/) to forward your localhost to a secure endpoint.

</alert>

## Order Created

This will scan for all `line_items` and refetch the product data. 

Your URL should point to the following endpoint:

```bash
https://YOURSITE/!/shopify/webhooks/order
```

## Product Delete

If you want products to be removed from Statamic whenever you delete them from Shopify, you can add a webhook in Shopify which tells the system to delete the products.

Your URL should point to the following endpoint:

```bash
https://YOURSITE/!/shopify/webhooks/product-deletion
```