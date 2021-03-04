import client from './client.js'

/**
 * Create the instance of the checkout for the user.
 * Let's first check if this exists or not in the storage.
 * Returns the ID for use elsewhere.
 *
 * @returns {object}
 */
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
