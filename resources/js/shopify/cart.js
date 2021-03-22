import client from './client'
import { checkoutId } from './checkout'
import {
  htmlToElements,
  formatCurrency,
  bannerMessage,
  debounce
} from './helpers'

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
const showCartOverview = async (lineItems, price, checkoutLink) => {
  cartView.classList.remove('hidden')
  noItemsMessage.classList.add('hidden')

  // Table
  const tableBody = document.querySelector('#ss-cart-view table tbody')

  // Append line item elements
  await lineItems.forEach(({ id, variant, title, quantity }) => {
    const price = formatCurrency(variant.price)
    const subtotal = formatCurrency(quantity * variant.price)

    let html = `<tr data-ss-variant-id="${id}">
    <td class="px-6 py-4 whitespace-nowrap" colspan="2">
        <div class="flex items-center">
            <div class="mr-3">
                <picture class="aspect-w-1 aspect-h-1 overflow-hidden block relative w-20 h-20">`

    if (variant.image) {
      html =
        html +
        `<img src="${variant.image.src}" class="pin-0 absolute object-cover" />`
    }

    html =
      html +
      `</picture>
    </div>
    <div>
        <span class="block font-semibold">${title}</span>
        <span>${variant.title}</span>
    </div>
</div>
</td>
<td class="px-6 py-4 whitespace-nowrap">
    ${price}
</td>
<td class="px-6 py-4 whitespace-nowrap">
    <input type="number" name="qty" min="1" class="border w-20 p-1" value="${quantity}"/>
</td>
<td class="px-6 py-4 whitespace-nowrap">
    ${subtotal}
</td>
    <td class="px-6 py-4 whitespace-nowrap">
        <a href="#" data-ss-delete class="text-red-600"><svg class="w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></a>
    </td>
</tr>`

    const elements = htmlToElements(html)

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
const updateQtyInStorefront = debounce((row, qty) => {
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
}, 500)

/**
 * Cart initialisation script which groups all the
 * functions above.
 */
const cart = () => {
  if (cartHolder == null && cartView == null) {
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
