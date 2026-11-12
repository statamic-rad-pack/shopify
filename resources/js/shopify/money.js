/**
 * Format a price for display, using the settings {{ shopify:tokens }} outputs.
 *
 * With `money_format` set to 'intl' (window.shopifyConfig.moneyFormat === 'intl') the
 * price is formatted for the site's locale and currency, e.g. £1,234.50, 1.234,50 € or
 * ¥1,235. Otherwise it's the currency symbol and the amount to 2 decimal places, e.g.
 * £1234.50, as in earlier versions.
 *
 * @param {number|string} amount
 * @param {string|null} currencyCode ISO 4217 code, e.g. from the cart's cost.totalAmount.currencyCode
 * @param {object} config defaults to window.shopifyConfig
 */
const formatMoney = (amount, currencyCode = null, config = (typeof window !== 'undefined' ? window.shopifyConfig : null) ?? {}) => {
    const value = parseFloat(amount);
    const code = currencyCode || config.currencyCode;

    if (config.moneyFormat === 'intl' && code && typeof Intl !== 'undefined') {
        try {
            return new Intl.NumberFormat(config.locale || undefined, { style: 'currency', currency: code }).format(value);
        } catch (e) {
            // an unknown locale or currency code falls back to the symbol format
        }
    }

    return (config.currency || '$') + value.toFixed(2);
};

/**
 * The localStorage key the cart id is kept under. Multi-store sites get one cart per
 * store; single-store sites keep the original key.
 */
const cartStorageKey = (config = (typeof window !== 'undefined' ? window.shopifyConfig : null) ?? {}) => {
    return 'statamic.shopify.cart.id' + (config.store ? '.' + config.store : '');
};

export {
    cartStorageKey,
    formatMoney,
};
