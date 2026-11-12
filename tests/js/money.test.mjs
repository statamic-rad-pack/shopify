import assert from 'node:assert/strict';
import { test } from 'node:test';
import { cartStorageKey, formatMoney } from '../../resources/js/shopify/money.js';

test('the default symbol format is unchanged', () => {
    assert.equal(formatMoney('1234.5', 'GBP', { currency: '£' }), '£1234.50');
    assert.equal(formatMoney(10, null, { currency: '€' }), '€10.00');
    assert.equal(formatMoney(10, null, {}), '$10.00');
});

test('the intl format uses the locale and currency code', () => {
    const config = { moneyFormat: 'intl', locale: 'en-GB', currency: '£' };

    assert.equal(formatMoney('1234.5', 'GBP', config), '£1,234.50');
    assert.equal(formatMoney('1234.5', 'JPY', { ...config, locale: 'ja-JP' }), '￥1,235');
    assert.equal(formatMoney('1234.5', 'EUR', { ...config, locale: 'de-DE' }).replace(/\s/g, ' '), '1.234,50 €');
});

test('the intl format falls back to the config currency code, then the symbol format', () => {
    assert.equal(formatMoney(5, null, { moneyFormat: 'intl', locale: 'en-US', currencyCode: 'USD' }), '$5.00');
    assert.equal(formatMoney(5, null, { moneyFormat: 'intl', currency: '£' }), '£5.00');
    assert.equal(formatMoney(5, 'NOT-A-CODE', { moneyFormat: 'intl', currency: '£' }), '£5.00');
});

test('single store sites keep the original cart key; multi-store sites get one per store', () => {
    assert.equal(cartStorageKey({}), 'statamic.shopify.cart.id');
    assert.equal(cartStorageKey({ store: 'uk' }), 'statamic.shopify.cart.id.uk');
});
