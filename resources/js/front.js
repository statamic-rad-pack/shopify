import Client from 'shopify-buy';

const client = Client.buildClient({
    domain: window.shopifyUrl,
    storefrontAccessToken: window.shopifyToken
});

window.shopifyClient = client;

// Create a checkout
let shopifyCheckout = localStorage.getItem('statamic.shopify.cart.id');

if (! shopifyCheckout) {
    client.checkout.create().then((checkout) => {
        localStorage.setItem('statamic.shopify.cart.id', checkout.id)
        shopifyCheckout = checkout.id
    })
}

/**
 * Fetch the cart and then dispatch the cart count.
 */
const fetchCart = () => {
    const checkoutId = localStorage.getItem('statamic.shopify.cart.id')

    client.checkout.fetch(checkoutId).then((checkout) => {
        setCartCount(checkout.lineItems);
    })
}

fetchCart()

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
            const elements = htmlToElements('<p>Product added to the basket. <a href="/cart">Go to cart</a></p>')

            bannerMessage(elements, true)

            setCartCount(checkout.lineItems)
        });
    }
}

new SSAddProductToCart();

// Fetch Cart Items
class SSCartContents {
    constructor() {
        this.checkoutId = localStorage.getItem('statamic.shopify.cart.id')
        this.cartLoading = document.getElementById('ss-cart-loading')
        this.noItemsMessage = document.getElementById('ss-cart-no-items')
        this.cartView = document.getElementById('ss-cart-view')
        this.cartHolder = document.getElementById('ss-cart')

        if (this.cartHolder != null && this.cartView != null) {
            this.initCart()
        }
    }

    initCart() {
        client.checkout.fetch(this.checkoutId).then((checkout) => {
            const { lineItems, subtotalPriceV2, webUrl } = checkout

            this.cartLoading.classList.add('hidden')

            if (lineItems.length === 0) {
                this.hideCart()
                return
            }

            // Show Elements
            this.showCart(lineItems, subtotalPriceV2, webUrl)
        });
    }

    hideCart() {
        this.noItemsMessage.classList.remove('hidden')
    }

    showCart(lineItems, price, checkoutLink) {
        this.cartView.classList.remove('hidden')

        // Table
        const tableBody = document.querySelector('#ss-cart-view table tbody');

        lineItems.forEach(item => {
            console.log(item)

            const elements = htmlToElements(`<tr data-ss-variant-id="${item.id}">
<td class="p-2"><img src="${item.variant.image.src}" class="w-20"/></td>
<td class="p-2"><span class="block font-semibold">${item.title}</span><span>${item.variant.title}</span></td>
<td class="p-2">${this.formatCurrency(item.variant.price)}</td>
<td class="p-2">${item.quantity}</td>
<td class="p-2">${this.formatCurrency(item.quantity * item.variant.price)}</td>
<td class="p-2"><a href="#" data-ss-delete class="text-sm text-red-600 uppercase">Delete</a></td>
</tr>`)
            elements.forEach(el => {
                tableBody.appendChild(el)
            })
        })

        this.initDeleteButtons()

        // Set subtotal value
        const subtotalEl = document.querySelector('[data-ss-subtotal]')
        subtotalEl.innerHTML = this.formatCurrency(price.amount)

        // Checkout Link
        const checkoutTag = document.getElementById('ss-checkout-link')
        checkoutTag.setAttribute('href', checkoutLink)
    }

    createCellRow(value, type = 'string') {
        const cell = document.createElement('td');
        let cellInner;

        cell.classList.add('p-2', 'border-b')

        if (type === 'image') {
            cellInner = document.createElement('img')
            cellInner.setAttribute('src', value)
            cellInner.classList.add('w-40')
        } else {
            cellInner = document.createTextNode(value)
        }

        cell.appendChild(cellInner)
        return cell
    }

    formatCurrency(price) {
        return parseFloat(price).toFixed(2)
    }

    initDeleteButtons() {
        const tableRows = document.querySelectorAll("#ss-cart-view table tbody tr")
        const tableRowsArray = Array.from(tableRows)

        console.log(tableRows);

        tableRowsArray.forEach(row => {
            const btn = row.querySelector("[data-ss-delete]");

            btn.addEventListener('click', e => {
                e.preventDefault();

                const id = row.getAttribute('data-ss-variant-id');
                this.deleteRowFromStorefront(id, row)
            })
        })
    }

    deleteRowFromStorefront(id, row) {
        const items = [];
        items.push(id)

        console.log({checkoutId: this.checkoutId, rowId: id })
        client.checkout.removeLineItems(this.checkoutId, items).then((checkout) => {
            const { lineItems } = checkout

            // Do something with the updated checkout
            setCartCount(lineItems)
            bannerMessage(htmlToElements('<p>Item removed successfully</p>'))

            if (lineItems.length === 0) {
                this.noItemsMessage.classList.remove('hidden')
                this.cartView.classList.add('hidden')
            }

            row.remove()
        });
    }
}

new SSCartContents()

/**
 * HTML to elements
 *
 * @param html
 * @returns {NodeListOf<ChildNode>}
 */
const htmlToElements = (html) => {
    let template = document.createElement('template');
    template.innerHTML = html;
    return template.content.childNodes;
}

/**
 * Set the banner message
 *
 * @param elements
 * @param type
 */
const bannerMessage = (elements, type = 'success') => {
    const banner = document.getElementById('ss-banner-message')
    banner.innerHTML = '' // remove if there is already content.
    banner.classList.remove('hidden')

    if (type === 'error') {
        banner.classList.add('bg-red-300')
    } else {
        banner.classList.add('bg-green-300')
    }

    elements.forEach(el => {
        banner.appendChild(el)
    })

    setTimeout(() => {
        banner.innerHTML = ''
        banner.classList.remove('bg-red-300', 'bg-green-300')
        banner.classList.add('hidden')
    }, 6000)
}

const setCartCount = (lineItems) => {
    let count = 0;
    const countTarget = document.querySelector('[data-ss-cart-count]')

    lineItems.forEach(item => {
      count = count + item.quantity
    })

    countTarget.innerHTML = count
}
