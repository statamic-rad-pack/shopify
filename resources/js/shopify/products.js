import client from './client'
import { htmlToElements, bannerMessage } from './helpers'
import { setCartCount } from './cart'
import { checkoutId } from './checkout'

/**
 * Initialise the Product form which handles the
 * adding to the basket.
 */
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

/**
 * When the form is submitted, lets make sure we add everything
 * to the shopify cart and then make sure our cart count is updated.
 * We also flash the user a banner message to say if it's added right.
 *
 * @param form
 */
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

export { handleProductFormSubmit }

export default productForm
