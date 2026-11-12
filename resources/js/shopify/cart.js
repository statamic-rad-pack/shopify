import client from './client.js';

const cartQuery = `
      id
      buyerIdentity {
        countryCode
      }
      checkoutUrl
      cost {
        totalAmount {
          amount
          currencyCode
        }
      }
      lines(first: 250) {
        edges {
          node {
            attributes {
                key
                value
            }
            cost {
              amountPerQuantity {
                amount
                currencyCode
              }
            }
            id
            merchandise {
              ... on ProductVariant {
                id
                image {
                  url
                }
                title
                product {
                   title
                }
                sku
              }
            }
            quantity
          }
        }
      }
      note
`;

/**
 * The market country from {{ shopify:tokens store="GB" }} (markets mode only).
 */
const marketCountry = () => (typeof window !== 'undefined' ? window.shopifyConfig?.country : null) || null;

/**
 * Build an operation, adding Shopify's @inContext(country:) directive when a market
 * country is set, so prices and currency match that market. Without one the
 * operation is the same as before.
 */
const operation = (type, name, params, body) => {
    const country = marketCountry();
    const allParams = country ? [...params, '$country: CountryCode!'] : params;
    const signature = allParams.length ? `(${allParams.join(', ')})` : '';

    return `${type} ${name}${signature}${country ? ' @inContext(country: $country)' : ''} {
      ${body}
    }`;
};

/**
 * Let the page know a cart request failed, e.g. to show a message:
 * window.addEventListener('shopify.cart.error', e => console.log(e.detail.operation, e.detail.errors))
 */
const reportError = (name, errors) => {
    console.warn(errors);

    if (typeof window !== 'undefined' && typeof window.dispatchEvent === 'function' && typeof CustomEvent !== 'undefined') {
        window.dispatchEvent(new CustomEvent('shopify.cart.error', { detail: { operation: name, errors } }));
    }
};

/**
 * Run an operation and return data[name], or undefined (after reporting) when the
 * request fails, returns GraphQL errors or the mutation returns userErrors.
 */
const run = async (name, query, variables = {}) => {
    const country = marketCountry();

    let response;

    try {
        response = await client.request(query, { variables: country ? { ...variables, country } : variables });
    } catch (error) {
        reportError(name, [error]);

        return;
    }

    const { data, errors } = response;

    if (errors) {
        reportError(name, errors);

        return;
    }

    const result = data?.[name];

    if (result?.userErrors?.length) {
        reportError(name, result.userErrors);

        return;
    }

    return result;
};

const userErrors = `userErrors {
        field
        message
      }`;

const addLines = async (cartId, lines) => {
    const query = operation('mutation', 'cartLinesAdd', ['$cartId: ID!', '$lines: [CartLineInput!]!'], `
      cartLinesAdd(cartId: $cartId, lines: $lines) {
        cart {
          ${cartQuery}
        }
        ${userErrors}
      }`);

    return (await run('cartLinesAdd', query, { cartId, lines }))?.cart;
};

const createFreshCart = async (cartLines = []) => {
    const query = operation('mutation', 'cartCreate', ['$cartInput: CartInput'], `
      cartCreate(input: $cartInput) {
        cart {
          ${cartQuery}
        }
        ${userErrors}
      }`);

    const cartInput = { lines: cartLines };

    if (marketCountry()) {
        cartInput.buyerIdentity = { countryCode: marketCountry() };
    }

    return (await run('cartCreate', query, { cartInput }))?.cart;
};

const getExistingCart = async (id) => {
    const query = operation('query', 'cart', ['$id: ID!'], `
      cart(id: $id) {
        ${cartQuery}
      }`);

    return await run('cart', query, { id });
};

const getOrCreateCart = async (cartId = null) => {
    let cart;

    if (cartId) {
        if (cart = await getExistingCart(cartId)) {
            // markets mode: a cart from another market's page (e.g. after switching
            // market by navigating) is moved to this page's market
            const country = marketCountry();

            if (country && cart.buyerIdentity?.countryCode !== country) {
                return (await updateBuyerCountry(cart.id, country)) ?? cart;
            }

            return cart;
        }
    }

    if (cart = await createFreshCart()) {
        return cart;
    }

    return false;
};

const removeLine = async (cartId, lineId) => {
    const query = operation('mutation', 'cartLinesRemove', ['$cartId: ID!', '$lineIds: [ID!]!'], `
      cartLinesRemove(cartId: $cartId, lineIds: $lineIds) {
        cart {
          ${cartQuery}
        }
        ${userErrors}
      }`);

    return (await run('cartLinesRemove', query, { cartId, lineIds: [lineId] }))?.cart;
};

const setCartAttributes = async (cartId, attributes) => {
    const query = operation('mutation', 'cartAttributesUpdate', ['$cartId: ID!', '$attributes: [AttributeInput!]!'], `
      cartAttributesUpdate(cartId: $cartId, attributes: $attributes) {
        cart {
          ${cartQuery}
        }
        ${userErrors}
      }`);

    return (await run('cartAttributesUpdate', query, { cartId, attributes }))?.cart;
};

const setCartNote = async (cartId, note) => {
    const query = operation('mutation', 'cartNoteUpdate', ['$cartId: ID!', '$note: String!'], `
      cartNoteUpdate(cartId: $cartId, note: $note) {
        cart {
          ${cartQuery}
        }
        ${userErrors}
      }`);

    return (await run('cartNoteUpdate', query, { cartId, note }))?.cart;
};

const updateLineQuantity = async (cartId, lineId, quantity) => {
    quantity = parseInt(quantity);

    if (quantity < 1) {
        return removeLine(cartId, lineId);
    }

    const query = operation('mutation', 'cartLinesUpdate', ['$cartId: ID!', '$lines: [CartLineUpdateInput!]!'], `
      cartLinesUpdate(cartId: $cartId, lines: $lines) {
        cart {
          ${cartQuery}
        }
        ${userErrors}
      }`);

    return (await run('cartLinesUpdate', query, { cartId, lines: [{ id: lineId, quantity }] }))?.cart;
};

/**
 * Move a cart to another market's country (markets mode), so it's priced in that
 * market's currency at checkout.
 */
const updateBuyerCountry = async (cartId, countryCode) => {
    const query = operation('mutation', 'cartBuyerIdentityUpdate', ['$cartId: ID!', '$buyerIdentity: CartBuyerIdentityInput!'], `
      cartBuyerIdentityUpdate(cartId: $cartId, buyerIdentity: $buyerIdentity) {
        cart {
          ${cartQuery}
        }
        ${userErrors}
      }`);

    return (await run('cartBuyerIdentityUpdate', query, { cartId, buyerIdentity: { countryCode } }))?.cart;
};

export {
    addLines,
    createFreshCart,
    getExistingCart,
    getOrCreateCart,
    removeLine,
    setCartAttributes,
    setCartNote,
    updateBuyerCountry,
    updateLineQuantity,
};
