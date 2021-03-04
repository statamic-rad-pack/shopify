import checkout from './shopify/checkout'
import cart, { setCartCount } from './shopify/cart'
import productForm from './shopify/products'

new checkout()
new setCartCount()
new productForm()
new cart()
