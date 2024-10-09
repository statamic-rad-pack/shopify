import { getOrCreateCart, removeLine } from '../cart'
import { checkoutId } from './checkout'

import {
  htmlToElements,
  formatCurrency,
  bannerMessage,
  debounce,
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

  getOrCreateCart(checkoutId)
    .then(({ lines }) => {
      let count = 0
      lineItems.forEach((item) => (count = count + item.quantity))
      countTarget.innerHTML = count
    })
    .catch((err) => {
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
  await lineItems.forEach(lineItem => {
    lineItem = lineItem.edges.node;
    const price = formatCurrency(lineItem.cost.amountPerQuantity.amount)
    const subtotal = formatCurrency(lineItem.quantity * lineItem.cost.amountPerQuantity.amount)

    let html = `<tr data-ss-variant-id="${lineItem.id}">
    <td class="px-6 py-4 whitespace-nowrap" colspan="2">
        <div class="flex items-center">
            <div class="mr-3">
                <picture class="aspect-square overflow-hidden block relative w-20 h-20">`

    if (lineItem.merchandise.image) {
      html =
        html +
        `<img src="${lineItem.merchandise.image.url}" class="pin-0 absolute object-cover" />`
    }

    html =
      html +
      `</picture>
    </div>
    <div>
        <span class="block font-semibold">${lineItem.merchandise.product.title}</span>
        <span>${lineItem.merchandise.title}</span>
    </div>
</div>
</td>
<td class="px-6 py-4 whitespace-nowrap">
    ${price}
</td>
<td class="px-6 py-4 whitespace-nowrap">
    <input type="number" name="qty" min="1" class="border w-20 p-1" value="${lineItem.quantity}"/>
</td>
<td class="px-6 py-4 whitespace-nowrap" data-ss-line-total>
    ${subtotal}
</td>
    <td class="px-6 py-4 whitespace-nowrap">
        <a href="#" data-ss-delete class="text-red-600"><svg class="w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></a>
    </td>
</tr>`

    const elements = htmlToElements(html)

    elements.forEach((el) => {
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
const setCartSubtotal = (amount) => {
  const subtotalEl = document.querySelector('[data-ss-subtotal]')

  if (subtotalEl != null) {
    subtotalEl.innerHTML = formatCurrency(amount)
  }
}

/**
 * Set the subtotal for a given cart line on the page.
 *
 * @param amount
 */
const setCartLineSubtotal = (id, amount) => {
  const subtotalEl = document.querySelector('[data-ss-variant-id="' + id + '"] [data-ss-line-total]')

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

  tableRows.forEach((row) => {
    const btnEls = row.querySelector('[data-ss-delete]')
    const qtyEls = row.querySelector('input[name=qty]')

    btnEls.addEventListener('click', (e) => {
      e.preventDefault()
      deleteRowFromStorefront(row)
    })

    qtyEls.addEventListener('change', (e) => {
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
const deleteRowFromStorefront = (row) => {
  const id = row.getAttribute('data-ss-variant-id')
  const items = []
  items.push(id)
  
  removeLine(checkoutId, id)
    .then(({ lines, cost }) => {
      setCartCount(lines)
      setCartSubtotal(cost.totalAmount.amount)
      bannerMessage(htmlToElements('<p>Item removed successfully</p>'))

      if (lines.length === 0) {
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

  updateLineQuantity(checkoutId, id, parseInt(qty))
    .then(({ lines, cost }) => {
      setCartCount(lines)
      setCartSubtotal(cost.totalAmount.amount)
      lines.forEach(lineItem => {
        if (lineItem.id == id) {
          setCartLineSubtotal(id, lineItem.quantity *  lineItem.edges.node.cost.amountPerQuantity.amount)
        }
      });
    })
    .catch((err) => {})
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
  getOrCreateCart(checkoutId)
    .then((checkout) => {
      const { items, cost, checkoutUrl } = checkout

      cartLoading.classList.add('hidden')

      if (items.length === 0) {
        hideCartOverview()
        return
      }

      // Show Elements
      showCartOverview(items, cost, checkoutUrl)
    })
    .catch((err) => {})
}

export {
  showCartOverview,
  hideCartOverview,
  initCartActions,
  setCartSubtotal,
  setCartCount,
}

export default cart