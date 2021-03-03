import Client from 'shopify-buy';

console.log(window.shopifyDomain)
console.log(window.shopifyToken)

const client = Client.buildClient({
    domain: window.shopifyDomain,
    storefrontAccessToken: window.shopifyToken
});

console.log(client);

// Create a checkout
let shopifyCheckout = localStorage.getItem('statamic.shopify.cart.id');

if (! shopifyCheckout) {
    client.checkout.create().then((checkout) => {
        localStorage.setItem('statamic.shopify.cart.id', checkout.id)
        shopifyCheckout = checkout.id
    })
}

client.product.fetch(6131515818134).then((product) => {
    // Do something with the product
    console.log(product);
});

// Add Product To Cart
class SSAddProductToCart {
    constructor() {
        this.productForm = document.getElementById('ss-product-add-form')

        if (this.productForm != null) {
            this.initForm()
        }
    }

    initForm() {
        this.productForm.addEventListener('submit', e => {
            e.preventDefault()
            this.submitForm()
        })
    }

    submitForm() {
        const quantity = this.productForm.querySelector('#ss-product-qty')
        const variantId = this.productForm.querySelector('#ss-product-variant')

        console.log('================Line Items To Add==============')
        console.log(quantity.value)
        console.log(variantId.value)

        if (variantId == null) {
            return
        }

        const lineItemsToAdd = [{
            variantId: variantId.value,
            quantity: (quantity != null) ? parseInt(quantity.value) : 1
        }];

        client.checkout.addLineItems(
            localStorage.getItem('statamic.shopify.cart.id'),
            lineItemsToAdd
        ).then((checkout) => {
            console.log('================Basket Update==============')
            console.log(checkout.lineItems); // Array with one additional line item
        });
    }
}

new SSAddProductToCart();
