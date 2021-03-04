---
title: JS SDK
description: ''
category: Frontend
position: 12
---

The addon includes three theme files which can help you quickly spin up an example integration. These are rather simple usecases that give us enough to integrate the JavaScript and control adding products to our basket, updating it, and proceeding to checkout.

## Intro

The first thing you need is to setup the client. 

The `client.js` uses the tokens output by `{{ shopify_tokens }}`. If you have installed the `modular-scripts` you will need to install the `shopify-buy` before anything will compile.

<code-group>
  <code-block label="Yarn" active>

  ```bash
  yarn add shopify-buy
  ```

  </code-block>
  <code-block label="NPM">

  ```bash
  npm install shopify-buy --save
  ```

  </code-block>
</code-group>


## Client.js

Our `client.js` is a rather simple file, it uses the window attributes to create a connection to the Storefront API. You'll need to use this whenever you want to interact with the API, so we have seperated it into it's own JS file so you can import it where needed.

```js
import Client from 'shopify-buy'

/**
 * Set up a new version of the Shopify Buy this uses the
 * token values set by {{ shopify:tokens }} in your template.
 */
const client = Client.buildClient({
  domain: window.shopifyUrl,
  storefrontAccessToken: window.shopifyToken
})

export default client
```

## Checkout.js

Every user of the site will be assigned a unique cart ID. To remember this, we utilise the [localStorage](https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage) (you could also do this with cookies).

```js
import client from './client.js'

const checkout = () => {
  // Check if we have found anything in local storage.
  let shopifyCheckout = localStorage.getItem('statamic.shopify.cart.id')

  // If not, let's create a new checkout for the user and set it as the ID.
  if (!shopifyCheckout) {
    client.checkout.create().then(checkout => {
      localStorage.setItem('statamic.shopify.cart.id', checkout.id)
      shopifyCheckout = checkout.id
    })
  }

  return shopifyCheckout
}

const checkoutId = checkout()

export { checkoutId }

export default checkout
```

Everytime we call the `checkout()` method, we will check to see if the checkout exists. If not, we will create a new checkout for them.

We have also exported the `checkoutId` directly as this is all you will need to interact with the checkout in future calls.

## Product.js

We want products in our basket, so this script handles adding the variant and quantities of a product to your user's checkout.

### productForm

Firstly, we setup the product form and grab the ID of the page and then call the `handleProductFormSubmit` function.

```js
const productForm = () => {
  const form = document.getElementById('ss-product-add-form')

  if (form == null) {
    return
  }

  form.addEventListener('submit', e => {
    e.preventDefault()
    handleProductFormSubmit(form)
  })
}

export default productForm
```

### handleProductFormSubmit

To actually process the form, we look for the `#ss-product-qty` and `#ss-product-variant` fields in the form and then parse these so they can be passed to the Storefront API.

```js
const handleProductFormSubmit = form => {
  const quantity = form.querySelector('#ss-product-qty')
  const variantId = form.querySelector('#ss-product-variant')

  if (variantId == null) {
    return
  }

  const lineItemsToAdd = [
    {
      variantId: variantId.value,
      quantity: quantity != null ? parseInt(quantity.value) : 1
    }
  ]

  client.checkout
    .addLineItems(checkoutId, lineItemsToAdd)
    .then(checkout => {
      const elements = htmlToElements(
        '<p><span class="mr-2">Product added to the basket.</span><a href="/cart">Go to cart</a></p>'
      )
      bannerMessage(elements, true)
      setCartCount(checkout.lineItems)
    })
    .catch(err => {
      // Handle Errors here.
    })
}
```

<alert scope="info">

We use two functions `bannerMessage()` and `setCartCount()` which we will address further down the page. In short, these display feedback to the user.

</alert>

## Cart

This is the big one. We handle pulling the cart details into our site, as well as updating quantity, managing deletion of products and handing off to Shopify.

### setCartCount

In the demo theme, we add a count into the header so the user can see how many products are in the basket. This function returns the cart, checks the total quantity and sets the count to that value.

```js
const setCartCount = () => {
  const countTarget = document.querySelector('[data-ss-cart-count]')

  if (countTarget == null) {
    return
  }

  client.checkout
    .fetch(checkoutId)
    .then(({ lineItems }) => {
      let count = 0
      lineItems.forEach(item => (count = count + item.quantity))
      countTarget.innerHTML = count
    })
    .catch(err => {
      // Handle Errors here.
    })
}
```

### hideCartOverview

Toggles whether the cart should be displayed. This is used upon loading the cart page.

```js
const hideCartOverview = () => {
  noItemsMessage.classList.remove('hidden')
  cartView.classList.add('hidden')
}
```

### setCartSubtotal

Updates the subtotal count with the `amount` passed to it. In our case we pass the subTotal of the whole checkout.

```js
const setCartSubtotal = amount => {
  const subtotalEl = document.querySelector('[data-ss-subtotal]')

  if (subtotalEl != null) {
    subtotalEl.innerHTML = formatCurrency(amount)
  }
}
```

### initCartActions

Whenever we change or append rows to the table we need to reinitialise our actions so that we can update the product quantity or delete the product entirely. We search for all `tr` in the `#ss-cart-view` table.

```js
const initCartActions = () => {
  const tableRows = document.querySelectorAll('#ss-cart-view table tbody tr')

  tableRows.forEach(row => {
    const btnEls = row.querySelector('[data-ss-delete]')
    const qtyEls = row.querySelector('input[name=qty]')

    btnEls.addEventListener('click', e => {
      e.preventDefault()
      deleteRowFromStorefront(row)
    })

    qtyEls.addEventListener('change', e => {
      e.preventDefault()
      updateQtyInStorefront(row, e.target.value)
    })
  })
}
```

### showCartOverview

This function handles looping around the lineItems, appending the data from each item to a table, passing the price to the `setCartSubtotal()` function and setting the unique checkout URL so the user can be forwarded to Shopiy.

```js
const showCartOverview = (lineItems, price, checkoutLink) => {
  cartView.classList.remove('hidden')
  noItemsMessage.classList.add('hidden')

  // Table
  const tableBody = document.querySelector('#ss-cart-view table tbody')

  // Append line item elements
  lineItems.forEach(({ id, variant, title, quantity }) => {
    const price = formatCurrency(variant.price)
    const subtotal = formatCurrency(quantity * variant.price)
    const elements = htmlToElements(`<tr data-ss-variant-id="${id}">
<td class="p-2"><img src="${variant.image.src}" class="w-20"/></td>
<td class="p-2"><span class="block font-semibold">${title}</span><span>${variant.title}</span></td>
<td class="p-2">${price}</td>
<td class="p-2"><input type="number" name="qty" min="1" value="${quantity}"/></td>
<td class="p-2">${subtotal}</td>
<td class="p-2"><a href="#" data-ss-delete class="text-sm text-red-600 uppercase">Delete</a></td>
</tr>`)

    elements.forEach(el => {
      tableBody.appendChild(el)
    })
  })

  // Init delete/qty inputs we just appended
  initCartActions()

  // Set subtotal value
  setCartSubtotal(price.amount)

  // Checkout Link
  const checkoutTag = document.getElementById('ss-checkout-link')
  checkoutTag.setAttribute('href', checkoutLink)
}
```

We also pass the prices to `formatCurrency` which is another helper we've made and should be updated to match your stores currency.

### updateQtyInStorefront

If the user changes the line item quantity, we want to make sure everything is squared up on our end and then passed to the Storefront API. 

```js
const updateQtyInStorefront = (row, qty) => {
  // Get the storefront id off the row.
  const id = row.getAttribute('data-ss-variant-id')

  // Set the item array
  const items = [{id: id, quantity: parseInt(qty)}]

  client.checkout
    .updateLineItems(checkoutId, items)
    .then(({ lineItems, subtotalPriceV2 }) => {
      setCartCount(lineItems)
      setCartSubtotal(subtotalPriceV2.amount)
    })
    .catch(err => {
      // HANDLE ERRORS
    })
}
```

### deleteRowFromStorefront

If the user deletes a line item from their cart, we need to send that data to the Storefront API. This function handles the deletion in the API and then updates the user information on the client. We also display a `bannerMessage()` to let the user know that everything happened okay!

```js
const deleteRowFromStorefront = row => {
  const id = row.getAttribute('data-ss-variant-id')
  const items = []
  items.push(id)

  client.checkout
    .removeLineItems(checkoutId, items)
    .then(({ lineItems, subtotalPriceV2 }) => {
      setCartCount(lineItems)
      setCartSubtotal(subtotalPriceV2.amount)
      bannerMessage(htmlToElements('<p>Item removed successfully</p>'))

      if (lineItems.length === 0) {
        noItemsMessage.classList.remove('hidden')
        cartView.classList.add('hidden')
      }

      row.remove()
    })
}
```

### cart

The cart function wraps up all of our other code in this file, it ensures that if the cart exists on the page, we then fetch all the details from the Storefront API, hide any loading messages, and display everything as wanted.

```js
const cart = () => {
  if (cartHolder == null && cartView == null) {
    console.log('Something went wrong finding the form')
    return
  }

  // Fetch the cart
  client.checkout
    .fetch(checkoutId)
    .then(checkout => {
      const { lineItems, subtotalPriceV2, webUrl } = checkout
      cartLoading.classList.add('hidden')

      if (lineItems.length === 0) {
        hideCartOverview()
        return
      }

      showCartOverview(lineItems, subtotalPriceV2, webUrl)
    })
    .catch(err => {
      // handle errors
    })
}
```

## Helpers

There are a few helpers in the JavaScript that handle repeated functionality between the JS elements. These can be used anywhere.

### htmlToElements

This allows us to create elements easier when appending the line items data or banner messages.

```js
export const htmlToElements = html => {
  let template = document.createElement('template')
  template.innerHTML = html
  return template.content.childNodes
}
```

### bannerMessage

Display a banner message to the end user. This is useful for declaring succesful actions or errors.

<code-group>
  <code-block label="JS" active>

  ```js
  export const bannerMessage = (elements, type = 'success', timeout = 6000) => {
    const banner = document.getElementById('ss-banner-message')

    // remove if there is already content + unhide banner
    banner.innerHTML = ''
    banner.classList.remove('hidden')

    // Set type
    if (type === 'error') {
      banner.classList.add('bg-red-300')
    } else {
      banner.classList.add('bg-green-300')
    }

    // Append elements
    elements.forEach(el => {
      banner.appendChild(el)
    })

    // Hide after timeout
    setTimeout(() => {
      banner.innerHTML = ''
      banner.classList.remove('bg-red-300', 'bg-green-300')
      banner.classList.add('hidden')
    }, timeout)
  }
  ```

  </code-block>
  
  <code-block label="HTML">

  ```html
  <div id="ss-banner-message"></div>
  ```

  </code-block>
</code-group>

### formatCurrency

Simply put, this formats the currency so it is displayed in a sane way when building the cart tables.

```js
export const formatCurrency = price => {
  return '£' + parseFloat(price).toFixed(2)
}
```
