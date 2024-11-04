import client from './client';

const cartQuery = `
      id
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

const addLines = async (cartId, lines) => {
    const operation = `mutation cartLinesAdd($lines: [CartLineInput!]!) {
      cartLinesAdd(cartId: "${cartId}", lines: $lines) {
        cart {
          ${cartQuery}
        }
        userErrors {
          field
          message
        }
      }
    }`;

    const { data, errors } = await client.request(operation, {
        variables: {
            "lines": lines,
        }
    });

    if (errors) {
        console.warn(errors);

        return;
    }

    if (data.cartLinesAdd.userErrors.length > 0) {
        console.warn(errors);

        return;
    }

    return data.cartLinesAdd.cart;
};

const createFreshCart = async (cartLines = []) => {
    const operation = `mutation createCart($cartInput: CartInput) {
      cartCreate(input: $cartInput) {
        cart {
            ${cartQuery}
        }
      }
    }`;

    const { data, errors } = await client.request(operation, {
        variables: {
            "cartInput": {
                "lines": cartLines,
            }
        }
    });

    if (errors) {
        console.warn(errors);

        return;
    }

    if (data.cartCreate) {
        return data.cartCreate.cart;
    }

    return;
};

const getExistingCart = async (id) => {
    const operation = `{
        cart(id: "${id}") {
            ${cartQuery}
        }
    }`;

    const { data, errors } = await client.request(operation);

    if (errors) {
        console.warn(errors);

        return;
    }

    return data.cart;
}

const getOrCreateCart = async (cartId = null) => {
    let cart;

    if (cartId) {
        if (cart = await getExistingCart(cartId)) {
            return cart;
        }
    }

    if (cart = await createFreshCart()) {
        return cart;
    }

    return false;
};

const removeLine = async (cartId, lineId) => {
    const operation = `mutation cartLinesRemove($lineIds: [ID!]!) {
      cartLinesRemove(cartId: "${cartId}", lineIds: $lineIds) {
        cart {
          ${cartQuery}
        }
      }
    }`;

    const { data, errors } = await client.request(operation, {
        variables: {
            "lineIds": [lineId],
        }
    });

    if (errors) {
        console.warn(errors);

        return;
    }

    return data.cartLinesRemove.cart;
}

const setCartAttributes = async (cartId, attributes) => {
    const operation = `mutation cartAttributesUpdate($attributes: [AttributeInput!]!) {
      cartAttributesUpdate(attributes: $attributes, cartId: "${cartId}") {
        cart {
           ${cartQuery}
        }
      }
    }`;

    const { data, errors } = await client.request(operation, {
        variables: {
            "attributes": attributes,
        }
    });

    if (errors) {
        console.warn(errors);

        return;
    }

    return data.cartAttributesUpdate.cart;
}

const setCartNote = async (cartId, note) => {
    const operation = `mutation cartNoteUpdate($note: String!) {
      cartNoteUpdate(cartId: "${cartId}", note: $note) {
        cart {
           ${cartQuery}
        }
      }
    }`;

    const { data, errors } = await client.request(operation, {
        variables: {
            "note": note,
        }
    });

    if (errors) {
        console.warn(errors);

        return;
    }

    return data.cartNoteUpdate.cart;
}

const updateLineQuantity = async (cartId, lineId, quantity) => {
    quantity = parseInt(quantity);

    if (quantity < 1) {
        return removeLine(lineId);
    }

    const operation = `mutation cartLinesUpdate($lines: [CartLineUpdateInput!]!) {
      cartLinesUpdate(
        cartId: "${cartId}"
        lines: $lines
      ) {
        cart {
          ${cartQuery}
        }
      }
    }`;

    const { data, errors } = await client.request(operation, {
        variables: {
            "lines": [{
                id: lineId,
                quantity: quantity,
            }],
        }
    });

    if (errors) {
        console.warn(errors);

        return;
    }

    return data.cartLinesUpdate.cart;
};

export {
    addLines,
    createFreshCart,
    getExistingCart,
    getOrCreateCart,
    removeLine,
    setCartAttributes,
    setCartNote,
    updateLineQuantity,
};
