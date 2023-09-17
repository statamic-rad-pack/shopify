---
title: Actions
category: CMS
position: 5
---

There is currently one method built into the addon that will let you look up variables.

## Fetch Product Variables

You may want to add a way to search your variants to the frontend. An endpoint has been set up so you can query this via JavaScript.

#### Usage

```bash
SITEURL/!/shopify/variants/{product}?option1=VALUE&option2=VALUE&option3=VALUE
```

| Option             | Description   | Required  |
| -------------------| ------------- | --------- |
| `product`          | Product slug as stored in Statamic | Y |
| `option1`          | Value to search option1 for | Y |
| `option2`          | Value to search option2 for | N |
| `option3`          | Value to search option3 for | N |

#### Returned Data

```json
[{
  title: 'Example Title',
  storefront_id: '',
  price: '10.00',
  inventory_quantity: 10
}]
```

