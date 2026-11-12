/**
 * Runs the bundled cart.js against a real development store's Storefront API.
 *
 * Needs .env.live (SHOPIFY_APP_URL, SHOPIFY_STOREFRONT_TOKEN) in the package root:
 *   npm run test:js:live
 *
 * Creates a throwaway cart; carts expire on their own in Shopify.
 */
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import { test } from 'node:test';

const envFile = new URL('../../.env.live', import.meta.url);
const env = existsSync(envFile)
    ? Object.fromEntries(readFileSync(envFile, 'utf8').split('\n').filter(line => /^[A-Z_]+=/.test(line)).map(line => [line.slice(0, line.indexOf('=')), line.slice(line.indexOf('=') + 1).trim()]))
    : {};

const configured = env.SHOPIFY_APP_URL && env.SHOPIFY_STOREFRONT_TOKEN;
const shop = (env.SHOPIFY_APP_URL || '').replace(/^https?:\/\//, '').replace(/\/$/, '');

// a browser-like window for client.js and the error events
const errors = [];
globalThis.window = new EventTarget();
window.shopifyConfig = { url: shop, token: env.SHOPIFY_STOREFRONT_TOKEN, apiVersion: env.SHOPIFY_API_VERSION || '2026-01' };
window.addEventListener('shopify.cart.error', event => errors.push(event.detail));

const cart = await import('../../resources/js/shopify/cart.js');
const { default: client } = await import('../../resources/js/shopify/client.js');

const firstVariantId = async () => {
    const { data } = await client.request('{ products(first: 10) { nodes { variants(first: 2) { nodes { id availableForSale } } } } }');

    return data.products.nodes.flatMap(product => product.variants.nodes).find(variant => variant.availableForSale)?.id;
};

test('creates and updates a cart with every cart.js operation', { skip: ! configured && 'no .env.live' }, async () => {
    window.shopifyConfig.country = undefined;
    errors.length = 0;

    const variantId = await firstVariantId();
    assert.ok(variantId, 'the store needs a product available to the storefront');

    let current = await cart.createFreshCart();
    assert.ok(current?.id, 'cartCreate');
    assert.ok(current.cost.totalAmount.currencyCode, 'the cart returns its currency code');

    current = await cart.addLines(current.id, [{ merchandiseId: variantId, quantity: 1, attributes: [{ key: 'gift', value: 'yes' }] }]);
    assert.equal(current.lines.edges.length, 1, 'cartLinesAdd');
    assert.ok(current.lines.edges[0].node.cost.amountPerQuantity.currencyCode, 'line prices include the currency code');

    const lineId = current.lines.edges[0].node.id;

    current = await cart.updateLineQuantity(current.id, lineId, 2);
    assert.equal(current.lines.edges[0].node.quantity, 2, 'cartLinesUpdate');

    current = await cart.setCartNote(current.id, 'Leave at the door');
    assert.equal(current.note, 'Leave at the door', 'cartNoteUpdate');

    current = await cart.setCartAttributes(current.id, [{ key: 'source', value: 'live-test' }]);
    assert.ok(current.id, 'cartAttributesUpdate');

    assert.equal((await cart.getExistingCart(current.id)).id, current.id, 'cart query');
    assert.equal((await cart.getOrCreateCart(current.id)).id, current.id, 'getOrCreateCart reuses the cart');

    current = await cart.updateLineQuantity(current.id, lineId, 0);
    assert.equal(current.lines.edges.length, 0, 'a quantity of 0 removes the line');

    assert.deepEqual(errors, [], 'no errors were reported');
});

test('uses the market country for the cart in markets mode', { skip: ! configured && 'no .env.live' }, async () => {
    errors.length = 0;
    window.shopifyConfig.country = 'GB';

    const variantId = await firstVariantId();
    let current = await cart.createFreshCart([{ merchandiseId: variantId, quantity: 1 }]);
    assert.ok(current?.id, 'cartCreate with @inContext and buyerIdentity');

    current = await cart.updateBuyerCountry(current.id, 'US');
    assert.ok(current?.id, 'cartBuyerIdentityUpdate');

    window.shopifyConfig.country = 'US';
    assert.ok((await cart.getExistingCart(current.id))?.id, 'cart query with @inContext');

    assert.deepEqual(errors, [], 'no errors were reported');
    window.shopifyConfig.country = undefined;
});

test('moves a cart from another market to this page\'s market when it is reloaded', { skip: ! configured && 'no .env.live' }, async () => {
    errors.length = 0;

    // created on a GB market page
    window.shopifyConfig.country = 'GB';
    const variantId = await firstVariantId();
    const created = await cart.createFreshCart([{ merchandiseId: variantId, quantity: 1 }]);
    assert.equal(created.buyerIdentity.countryCode, 'GB');

    // then the shopper navigates to a US market page
    window.shopifyConfig.country = 'US';
    const reloaded = await cart.getOrCreateCart(created.id);

    assert.equal(reloaded.id, created.id, 'the same cart is kept');
    assert.equal(reloaded.buyerIdentity.countryCode, 'US', 'and moved to the page\'s market');
    assert.equal(reloaded.lines.edges.length, 1, 'with its lines');

    // outside markets mode the cart is left alone
    window.shopifyConfig.country = undefined;
    assert.equal((await cart.getOrCreateCart(created.id)).buyerIdentity.countryCode, 'US');

    assert.deepEqual(errors, [], 'no errors were reported');
});

test('reports failures with a shopify.cart.error event and returns nothing', { skip: ! configured && 'no .env.live' }, async () => {
    errors.length = 0;

    const current = await cart.createFreshCart();
    const result = await cart.addLines(current.id, [{ merchandiseId: 'gid://shopify/ProductVariant/1', quantity: 1 }]);

    assert.equal(result, undefined);
    assert.equal(errors.length, 1);
    assert.equal(errors[0].operation, 'cartLinesAdd');
});
