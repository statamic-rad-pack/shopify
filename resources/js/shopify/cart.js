import client from './client'
import { checkoutId } from './checkout'
import { htmlToElements, formatCurrency, bannerMessage } from './helpers'

const cartLoading = document.getElementById('ss-cart-loading')
const noItemsMessage = document.getElementById('ss-cart-no-items')
const cartView = document.getElementById('ss-cart-view')
const cartHolder = document.getElementById('ss-cart')

/**
 * Set the cart count for the item
 */
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

/**
 * Hides the cart overview.
 * Shows the message that nothing is in the basket.
 */
const hideCartOverview = () => {
  noItemsMessage.classList.remove('hidden')
  cartView.classList.add('hidden')
}

/**
 * Shows the cart overview.
 * Hides the message that nothing is in the basket.
 */
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

/**
 * Set the subtotal amount on the page.
 * Giving the user an overview of total basket.
 *
 * @param amount
 */
const setCartSubtotal = amount => {
  const subtotalEl = document.querySelector('[data-ss-subtotal]')

  if (subtotalEl != null) {
    subtotalEl.innerHTML = formatCurrency(amount)
  }
}

/**
 * Initialise the cart actions for updating qty
 * and deleting a product from the basket.
 * These are reloaded on any item change
 */
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

/**
 * Get ride of a row from the cart page and then
 * delete it from storefront.
 *
 * @param row
 */
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

/**
 * Update the quantity in Storefront + the cart
 *
 * @param row
 * @param qty
 */
const updateQtyInStorefront = (row, qty) => {
  const id = row.getAttribute('data-ss-variant-id')

  const items = [
    {
      id: id,
      quantity: parseInt(qty)
    }
  ]

  client.checkout
    .updateLineItems(checkoutId, items)
    .then(({ lineItems, subtotalPriceV2 }) => {
      setCartCount(lineItems)
      setCartSubtotal(subtotalPriceV2.amount)
    })
    .catch(err => {})
}

/**
 * Cart initialisation script which groups all the
 * functions above.
 */
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

      // Show Elements
      showCartOverview(lineItems, subtotalPriceV2, webUrl)
    })
    .catch(err => {})
}

export {
  showCartOverview,
  hideCartOverview,
  initCartActions,
  setCartSubtotal,
  setCartCount
}

export default cart
