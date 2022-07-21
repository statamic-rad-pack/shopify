import client from './client.js'

/**
 * Initialise a new checkout.
 */
const initNewCheckout = () => {
  client.checkout.create().then((checkout) => {
    localStorage.setItem('statamic.shopify.cart.id', checkout.id)
    shopifyCheckout = checkout.id
  })
}

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
  // If so, we make sure the checkout hasn't been completed.
  if (! shopifyCheckout) {
    initNewCheckout()
  } else {
    client.checkout.fetch(shopifyCheckout).then(checkout => {
      console.log(checkout)

      if (checkout.completedAt !== null) {
        localStorage.removeItem('statamic.shopify.cart.id')
      }

      initNewCheckout()
    })
  }

  return shopifyCheckout;
}

const checkoutId = checkout()

export {
  checkoutId
}

export default checkout
