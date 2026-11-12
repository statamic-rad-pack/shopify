import { addLines, getOrCreateCart, removeLine, setCartAttributes, setCartNote, updateBuyerCountry, updateLineQuantity } from './cart.js';
import { cartStorageKey, formatMoney } from './money.js';

const createStore = (Alpine) => {
    Alpine.store('statamic.shopify.cart', {
        cartId: null,
        checkoutUrl: '',
        currencyCode: null,
        lineItems: [],
        market: window.shopifyConfig?.market,
        note: '',
        subtotal: null,
        subtotalAmount: null,

        async init() {
            this.cartId = localStorage.getItem(cartStorageKey());

            if (this.cartId) {
                this.updateFromResponse(await getOrCreateCart(this.cartId));
            }

            window.addEventListener('shopify.updateMarket', (event) => {
                this.market = event.detail.market;

                // markets mode: move the cart to the new market so checkout uses its prices and currency
                if (window.shopifyConfig?.country && event.detail.market) {
                    window.shopifyConfig.country = event.detail.market.toUpperCase();

                    if (this.cartId) {
                        updateBuyerCountry(this.cartId, window.shopifyConfig.country).then(response => this.updateFromResponse(response));
                    }
                }
            });
        },

        formatCurrency(amount, currencyCode = null) {
            return formatMoney(amount, currencyCode ?? this.currencyCode);
        },

        getItems() {
            return this.lineItems;
        },

        removeLine(id) {
            removeLine(this.cartId, id).then(response => this.updateFromResponse(response));
        },

        setAttributes(attrs) {
            setCartAttributes(this.cartId, attrs).then(response => this.updateFromResponse(response));
        },

        setNote(note) {
            setCartNote(this.cartId, note).then(response => this.updateFromResponse(response));
        },

        updateQuantity(id, quantity) {
            updateLineQuantity(this.cartId, id, quantity).then(response => this.updateFromResponse(response));
        },

        updateFromResponse(checkout) {
            // failed requests resolve to nothing (see the shopify.cart.error event)
            if (! checkout) {
                return;
            }

            const currencyCode = checkout.cost.totalAmount.currencyCode;
            let mappedLineItems = [];
            for (let line of checkout.lines.edges) {
                line = line.node;

                let attrs = {};
                line.attributes.forEach((attr) => attrs[attr.key] = attr.value);

                let qty = line.quantity;
                let amount = parseFloat(line.cost.amountPerQuantity.amount);

                mappedLineItems.push({
                    id: line.id,
                    title: line.merchandise.product.title,
                    variant: {
                        title: line.merchandise.title,
                        id: line.merchandise.id,
                    },
                    price: this.formatCurrency(amount, currencyCode),
                    qty: qty,
                    subtotal: this.formatCurrency(amount * qty, currencyCode),
                    // unformatted values, for formatting prices yourself
                    amount: amount,
                    subtotalAmount: amount * qty,
                    currencyCode: currencyCode,
                    image: line.merchandise?.image?.url,
                    attributes: attrs,
                });
            }

            this.cartId = checkout.id;
            this.currencyCode = currencyCode;
            this.lineItems = mappedLineItems;
            this.note = checkout.note;
            this.subtotal = this.formatCurrency(checkout.cost.totalAmount.amount, currencyCode);
            this.subtotalAmount = parseFloat(checkout.cost.totalAmount.amount);
            this.checkoutUrl = checkout.checkoutUrl;

            localStorage.setItem(cartStorageKey(), this.cartId);
        }
    });
}

const createData = (Alpine) => {
    Alpine.data('shopifyProduct', (options, variants) => ({
        added: false,
        customAttributes: {},
        options: options,
        selected: {},
        selectedVariant: false,
        variants: variants,
        variantEl: null,

        init() {
            this.variantEl = this.$el.querySelector('[name="ss-product-variant"]');

            if (! variants.length) {
                return;
            }

            if (! options) {
                options = {};
                this.options = options;
            }

            this.selectedVariant = variants[0];
            
            if (this.variantEl) {
                this.variantEl.value = this.selectedVariant.slug;
            }

            for (const [key, value] of Object.entries(options)) {
                this.selected[key] = this.selectedVariant[key];
            }
        },

        allOptionsSelected() {
            let selectedValues = [];

            for (const [key, value] of Object.entries(options)) {
                if (this.selected[key] ?? false) {
                    selectedValues.push(this.selected[key]);
                }
            }

            return selectedValues.length == Object.values(options).length;
        },

        getOptions(index, value) {
            let vals = [];
            Object.values(variants).forEach((opt) => {
                vals.push(opt[index]);
            });

            return [... new Set(vals)];
        },

        async handleSubmit(target) {
            let variantId = this.variantEl.value;

            if (! variantId) {
                return;
            }

            let attributes = [];
            for (const key in this.customAttributes) {
                attributes.push({key: key, value: this.customAttributes[key] + '' });
            }

            let qty = parseInt(this.$refs.qty.value);

            let cart = await getOrCreateCart(Alpine.store('statamic.shopify.cart').cartId);

            // the error has been reported with a shopify.cart.error event
            if (! cart) {
                return;
            }

            let response = await addLines(cart.id, [{
                attributes: attributes,
                merchandiseId: "gid://shopify/ProductVariant/" + variantId,
                quantity: qty,
            }]);

            if (! response) {
                return;
            }

            Alpine.store('statamic.shopify.cart').updateFromResponse(response);

            this.added = true;
            setTimeout(() => this.added = false, 3500);
        },

        optionChange(index, value) {
            this.selected[index] = value;
        },

        getMarketData(variant) {
            if (! this.market || !variant.market_data) return null;
            return variant.market_data[this.market] ?? null;
        },

        getVariantPrice(variant) {
            return this.getMarketData(variant)?.price ?? variant.price;
        },

        outOfStock(variant) {
            const marketData = this.getMarketData(variant);
            const qty = marketData ? marketData.inventory_quantity : variant.inventory_quantity;
            return variant.inventory_policy && (variant.inventory_policy.toLowerCase() == 'deny') && (qty <= 0);
        },

        variantExistsAndIsInStock() {
            let filteredVariants = variants;

            for (const [key, value] of Object.entries(options)) {
                filteredVariants = filteredVariants.filter((variant) => {
                    return variant[key] == this.selected[key] ?? false;
                });
            }

            if (filteredVariants.length == 1) {
                this.variantEl.value = filteredVariants[0].slug;
                this.selectedVariant = filteredVariants[0];

                this.selectedVariant.out_of_stock = this.outOfStock(this.selectedVariant);

                return ! this.selectedVariant.out_of_stock;
            }

            if (this.selectedVariant) {
                this.selectedVariant.out_of_stock = this.outOfStock(this.selectedVariant);
            }

            return this.selectedVariant && ! this.selectedVariant.out_of_stock;
        },
    }));
};

export {
    createData,
    createStore,
};
