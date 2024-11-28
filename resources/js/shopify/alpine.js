import { addLines, getOrCreateCart, removeLine, setCartAttributes, setCartNote, updateLineQuantity } from './cart';
import Alpine from 'alpinejs';

const createStore = () => {
    Alpine.store('statamic.shopify.cart', {
        cartId: null,
        checkoutUrl: '',
        lineItems: [],
        note: '',
        subtotal: null,

        async init() {
            this.cartId = localStorage.getItem('statamic.shopify.cart.id');

            if (this.cartId) {
                this.updateFromResponse(await getOrCreateCart(this.cartId));
            }
        },

        formatCurrency(amount) {
            return '$' + parseFloat(amount).toFixed(2);
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
            let mappedLineItems = [];
            for (let line of checkout.lines.edges) {
                line = line.node;

                let attrs = {};
                line.attributes.forEach((attr) => attrs[key] = attr.value);

                let qty = line.quantity;

                mappedLineItems.push({
                    id: line.id,
                    title: line.merchandise.product.title,
                    variant: {
                        title: line.merchandise.title,
                    },
                    price:this.formatCurrency(line.cost.amountPerQuantity.amount),
                    qty: qty,
                    subtotal: this.formatCurrency(line.cost.amountPerQuantity.amount * qty),
                    image: line.merchandise?.image?.url,
                    attributes: attrs,
                });
            }

            this.cartId = checkout.id;
            this.lineItems = mappedLineItems;
            this.note = checkout.note;
            this.subtotal = this.formatCurrency(checkout.cost.totalAmount.amount);
            this.checkoutUrl = checkout.checkoutUrl;

            localStorage.setItem('statamic.shopify.cart.id', this.cartId);
        }
    });
}

const createData = () => {
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
            }

            this.selectedVariant = variants[0];
            this.variantEl.value = this.selectedVariant.slug;

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

            let response = await addLines(cart.id, [{
                attributes: attributes,
                merchandiseId: "gid://shopify/ProductVariant/" + variantId,
                quantity: qty,
            }]);

            Alpine.store('statamic.shopify.cart').updateFromResponse(response);

            this.added = true;
            setTimeout(() => this.added = false, 3500);
        },

        optionChange(index, value) {
            this.selected[index] = value;
        },

        outOfStock(variant) {
            return variant.inventory_policy == 'deny' && variant.inventory_management == 'shopify' && variant.inventory_quantity <= 0;
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
