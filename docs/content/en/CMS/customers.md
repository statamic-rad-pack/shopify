---
title: Customers
category: CMS
position: 2
---

If you want Shopify and Statamic to sync users and customers you need to change some of the default config values.

## Link Statamic customers from Shopify

To create a link between Shopify customers and Statamic users you need to ensure your Shopify app has `customer read` and `customer write` permissions.

You then need to set up the following webhooks:

### Customer Create

Add a webhook on **Customer Creation** that sends the data to Statamic and queues the import of that one customer.

Your URL should point to the following endpoint:

```bash
https://YOURSITE/!/shopify/webhook/customer/create
```

### Customer Update

Add a webhook on **Customer Update** that sends any updated data to Statamic and queues a refresh of that customer.

Your URL should point to the following endpoint:

```bash
https://YOURSITE/!/shopify/webhook/customer/update
```

### Customer Delete

This webhook will remove the connection between a user in Statamic and the now deleted customer in Shopify. It will NOT delete the user in Statamic.

Your URL should point to the following endpoint:

```bash
https://YOURSITE/!/shopify/webhook/customer/delete
```


## Allowing Shopify to create users in Statamic

Ig you want new user creation in Shopify to create a new user in Statamic, then in your shopify.php config file, you need to set `create_users_from_shopify` to true.

## Allowing Statamic to create and update users in Shopify

Ig you want user creation and changes in Statamic to modify Shopify users, then in your shopify.php config file, you need to set `update_users_in_shopify` to true.

If you want to modify more than just first_name, last_name, and email you can also optionally specify a different job for `update_shopify_user_job`.
