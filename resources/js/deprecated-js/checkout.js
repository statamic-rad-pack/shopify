import { getOrCreateCart } from '../cart'

const checkout = () => {
  let shopifyCheckout = localStorage.getItem('statamic.shopify.cart.id');

  getOrCreateCart(shopifyCheckout).then((checkout) => {
    localStorage.setItem('statamic.shopify.cart.id', checkout.id);
    shopifyCheckout = checkout.id;
  });

  return shopifyCheckout;
}

const checkoutId = checkout()

export {
  checkoutId
}

export default checkout