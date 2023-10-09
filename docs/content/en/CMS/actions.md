---
title: Actions
category: CMS
position: 5
---


## Fetch Product Variables

You may want to add a way to search your variants to the frontend. An endpoint has been set up so you can query this via JavaScript.

#### Usage

```bash
SITEURL/!/shopify/variants/{product}?option1=VALUE&option2=VALUE&option3=VALUE
```

| Option             | Description   | Required  |
| -------------------| ------------- | --------- |
| `product`          | Product slug as stored in Statamic | Y |
| `option1`          | Value to search option1 for | N |
| `option2`          | Value to search option2 for | N |
| `option3`          | Value to search option3 for | N |

#### Returned Data

```json
[{
  title: 'Example Title',
  storefront_id: '1234',
  price: '10.00',
  inventory_quantity: 10
}]
```


## Create a Customer Address

If you want to create a Shopify Address for a customer you can POST to

#### Usage

```bash
SITEURL/!/shopify/address
```

| Parameters             | Description   | Required  |
| -------------------| ------------- | --------- |
| `customer_id`          | customer_id in Shopify. Defaults to the logged in user's customer_id | N |
| `first_name`          | Address first name | Y |
| `last_name`          | Address last name | Y |
| `company`          | Address company | N |
| `address1`          | Address line 1 | Y |
| `address2`          | Address line 2 | N |
| `city`          | Address city | Y |
| `province`          | Address province / state | Y |
| `zip`          | Address zip / postal code | Y |
| `country`          | Address country name | N |
| `country_code`          | Address country code (length 2) | N |
| `phone`          | Address phone number | N |
| `name`          | Name or identifier for the address | N |
| `default`          | Make this the default address (boolean) | N |

#### Returned Data

```json
[{
  message: 'Address created',
  address: {...}
}]
```

## Update a Customer Address

If you want to create a Shopify Address for a customer you can POST to

#### Usage

```bash
SITEURL/!/shopify/address/{id}
```

| Parameters             | Description   | Required  |
| -------------------| ------------- | --------- |
| `customer_id`          | customer_id in Shopify. Defaults to the logged in user's customer_id | N |
| `first_name`          | Address first name | Y |
| `last_name`          | Address last name | Y |
| `company`          | Address company | N |
| `address1`          | Address line 1 | Y |
| `address2`          | Address line 2 | N |
| `city`          | Address city | Y |
| `province`          | Address province / state | Y |
| `zip`          | Address zip / postal code | Y |
| `country`          | Address country name | N |
| `country_code`          | Address country code (length 2) | N |
| `phone`          | Address phone number | N |
| `name`          | Name or identifier for the address | N |
| `default`          | Make this the default address (boolean) | N |

#### Returned Data

```json
[{
  message: 'Address updated',
  address: {...}
}]
```

## DElete a Customer Address

If you want to delete a Shopify Address for a customer you can send a DELETE request to

#### Usage

```bash
SITEURL/!/shopify/address/{id}
```

| Parameters             | Description   | Required  |
| -------------------| ------------- | --------- |
| `customer_id`          | customer_id in Shopify. Defaults to the logged in user's customer_id | N |

#### Returned Data

```json
[{
  message: 'Address deleted',
}]
```
