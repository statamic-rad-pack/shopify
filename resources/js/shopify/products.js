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

  form.addEventListener('submit', (e) => {
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
const handleProductFormSubmit = (form) => {
  const quantity = form.querySelector('#ss-product-qty')
  const variantId = form.querySelector('#ss-product-variant')

  if (variantId == null) {
    return
  }

  const lineItemsToAdd = [
    {
      variantId: variantId.value,
      quantity: quantity != null ? parseInt(quantity.value) : 1,
    },
  ]

  client.checkout
    .addLineItems(
      checkoutId,
      lineItemsToAdd
    )
    .then((checkout) => {
      const elements = htmlToElements(
        '<div class="text-center"><span class="mr-4">Product added to the basket.</span><a href="/cart" class="inline-flex items-center"><span>Go to cart</span> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="ml-2 w-4"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></a></div>'
      )
      bannerMessage(elements, true)
      setCartCount(checkout.lineItems)
    })
    .catch((err) => {
      // Handle Errors here.
    })
}

export { handleProductFormSubmit }

export default productForm
