---
title: Storefront API
description: ''
category: Frontend
position: 12
---

The add-on includes three theme files that can help you quickly spin up an example integration. These are rather simple use cases that give us enough to integrate the JavaScript and control adding products to our basket, updating it, and handing it off to Shopify for checkout.

The following breaks down the JavaScript included and the steps needed to get everything working.

## Publishing Assets

Before we can talk about what anything achieves, we need to publish the boilerplate assets. Run the following command to get the uncompiled JavaScript into your resources directory.

```bash
php artisan vendor:publish --tag="shopify-scripts"
```

For the theme files you can also run:

```bash
php artisan vendor:publish --tag="shopify-theme"
```

## Initiating

The first thing you need is to setup the client. 

The `client.js` uses the tokens output by `{{ shopify:tokens }}`. You will need to install the `@shopify/storefront-api-client` before anything will compile.

<code-group>
  <code-block label="Yarn" active>

  ```bash
  yarn add @shopify/storefront-api-client
  ```

  </code-block>
  <code-block label="NPM">

  ```bash
  npm install @shopify/storefront-api-client --save
  ```

  </code-block>
</code-group>


## client.js

Our `client.js` is a rather simple file, it uses the `shopifyConfig` created by `{{ shopify:tokens }}` to create a connection to Shopify's GraphQL API. You'll need to use this whenever you want to interact with the API, so we have separated it into it's own JS file so you can import it where needed.

```js
import client from 'vendor/shopify/client';
```

## cart.js
This file provides methods for creating, updating and reading from the cart associated with the current user. You can import any of the following methods. Note that all methods are `async`.


### createFreshCart

```js
createFreshCart(?array: lines)
```
Creates a brand new cart. If an array of cart lines are passed into the function, they will be added to the cart. 


### getExistingCart
```js
getExistingCart(string: cartId)
```
Returns the cart identified by the passed id.


### getOrCreateCart
```js
getOrCreateCart(?string: cartId)
```
Gets a cart that matches the id. If one cannot be found, then a new cart will be returned instead.


### setCartAttributes
```js
setCartAttributes(string: cartId, array: lines)
```
Set attributes associated with the cart. This will overwrite previously set attributes if you have any. This should be an array of objects consisting of a `key` and `value`. Eg:

```js
[
    { 
        key: 'my_key', 
        value: 'my_value' 
    },
    { 
        key: 'my_key', 
        value: 'my_value' 
    }
]
```


### setCartNote
```js
setCartNote(string: cartId, string: note)
```
Sets a note that will be attached to the cart. This will overwrite existing cart notes.


### addLines
```js
addLines(string: cartId, array: lines)
```
Adds an array of product lines to the cart. See [Shopify's documentation](https://shopify.dev/docs/api/storefront/2024-01/mutations/cartLinesAdd) for more information.


### removeLine
```js
removeLine(string cartId, string lineId)
```
Remove the line identified by line id from the cart.


### updateLineQuantity
```js
updateLineQuantity(string cartId, string lineId, int quantity)
```
Updates the quantity of a specified cart line. 


## alpine.js

This file provides an [Alpine.js](https://alpinejs.dev) store and helper to make getting up and running that much quicker. The published views assume this code is available and being included in your site.js:

```js
import { createData, createStore } from './vendor/shopify/alpine';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

createStore();
createData();

Alpine.start();
```

### Alpine.data('shopifyProduct')
This Alpine helper helps you take the output of `{{ shopify:variant:generate }}` and turn into a dependent set of drop downs. In addition it provides some useful methods for checking if the selected variant is in stock, or requires further options to be selected.

To use it:

```antlers
{{ $variants = {shopify:variants} }}
<div x-data='statamic.shopify.product({{ options | to_json }}, {{ variants | to_json }})'>

	<form @submit.prevent="handleSubmit($event.target)">
        <input type="hidden" name="product_id" id="ss-product-id" value="{{ product_id }}" />
    
        {{ shopify:variants:generate show_price="true" show_out_of_stock="true"  }}
    
        <div x-show="variants.length > 1">
            <template x-for="(option, index) in options">
                <div>
                    <label x-text="option"></label>
    
                    <select @change="optionChange(index, $event.target.value)">
                        <option disabled x-text="'Choose ' + option"></option>
                        <template x-for="value in getOptions(index)">
                            <option :value="value" x-text="value" :selected="(selected[index] ?? false) == value">
                        </template>
                    </select>
    
                </div>
            </template>
        </div>
    
        <div>
            <input type="number" min="1" value="1" x-ref="qty" name="quantity" />
        </div>
    
        <button type="submit" :disabled="! (allOptionsSelected() && variantExistsAndIsInStock())">Add to Cart</button>
    </form>

</div>

 ```

The `handleSubmit` method makes use of the `cart.js` methods to update the cart with the line items to be added.


### Alpine.store('statamic.shopify.cart')
This store provides client side cart functionality and methods that are needed to allow users to update their cart on the site.

The cart reference is stored in localStorage so that it persists across page loads and new carts are not unecessarily created.

This snippet from the cart.antlers.html shows you how it can be used to display the line items in the basket to the end user.

```antlers
<template x-for="line in Alpine.store('statamic.shopify.cart').lineItems">
    <tr>
        <td colspan="2">
            <div class="flex items-center">
                <div class="mr-3">
                    <picture class="aspect-square overflow-hidden block relative w-20 h-20" x-show="line.image">
                        <img :src="line.image" :alt="line.title" loading="lazy" class="pin-0 absolute object-cover">
                    </picture>
                </div>
                <div>
                    <span class="block font-semibold" x-text="line.title"></span>
                    <spanx-text="line.variant.title"></span>
                </div>
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap" x-html="line.price"></td>
        <td class="px-6 py-4 whitespace-nowrap">
            <input type="number" name="qty" min="1" class="border w-20 p-1" :value="line.qty" @change="Alpine.store('cart').updateQuantity(line.id, $event.target.value)" />
        </td>
        <td class="px-6 py-4 whitespace-nowrap" x-html="line.subtotal"></td>
        <td class="px-6 py-4 whitespace-nowrap">
            <a href="#" @click.prevent="Alpine.store('cart').removeLine(line.id)" class="text-red-600"><svg class="w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></a>
        </td>
    </tr>
</template>

 ```

	
