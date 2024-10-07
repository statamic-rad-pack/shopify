import client from './client';

const cartQuery = `
      id
      checkoutUrl
      completedAt
      lines(first: 1000) {
        edges {
          node {
            id
            merchandise {
              ... on ProductVariant {
                id
                quantity
                title
              }
            }
          }
        }
      }
      cost {
        totalAmount {
          amount
          currencyCode
        }
      }
`;

const addLines = (cartId, lines) => {
    const operation = `mutation cartLinesAdd($cartId: ID!, $lines: [CartLineInput!]!) {
      cartLinesAdd(cartId: ${cartId}, lines: $lines) {
        cart {
          ${cartQuery}
        }
        userErrors {
          field
          message
        }
      }
    }`;

    const { data, errors } => await client.request(operation, {
        variables: {
            "lines": lines,
        }
    });

    if (errors) {
        console.warn(errors);

        return;
    }

    if (data.userErrors !== null) {
        console.warn(errors);

        return;
    }

    return data.cart;
};

const cart = (cartId) => {
    if (cartId) {
        if (cart = getExistingCart(cartId)) {
            return cart;
        }
    }

    if (cart = createFreshCart()) {
        return cart;
    }

    return false;
};

const createFreshCart = (cartLines = []) => {
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

    if (data.createCart) {
        return data.createCart;
    }

    return;
};

const getCartCount = () => {
    let cart = cart();

    if (! cart) {
        return 0;
    }

    return (cart.lines?.edges ?? []).reduce((accumulator, node) => (accumulator + node.quantity), 0);
}

const getExistingCart = (id) => {
    const operation = `{
        cart(id: "gid://shopify/Cart/${id}") {
            ${cartQuery}
        }
    }`;

    const { data, errors } => await client.request(operation);

    if (errors) {
        console.warn(errors);

        return;
    }

    if (data.cart.completedAt !== null) {
        return;
    }

    return data.cart;
}

const removeLine = (cartId, lineId) => {
    const operation = `mutation cartLinesRemove($cartId: ID!, $lineIds: [ID!]!) {
      cartLinesRemove(cartId: ${cartId}, lineIds: $lineIds) {
        cart {
          ${cartQuery}
        }
        userErrors {
          field
          message
        }
        warnings {
        }
      }
    }`;

    const { data, errors } => await client.request(operation, {
        variables: {
            "linesIds": ["gid://shopify/CartLine/${lineId}"],
        }
    });

    if (errors) {
        console.warn(errors);

        return;
    }

    if (data.userErrors !== null) {
        console.warn(errors);

        return;
    }

    return data.cart;
}

const updateLineQuantity = (cartId, lineId, quantity) => {
    quantity = parseInt(quantity);

    if (quantity < 1) {
        return removeLine(lineId);
    }

    const operation = `mutation {
      cartLinesUpdate(
        cartId: "${cartId}"
        lines: $lines
      ) {
        cart {
          ${cartQuery}
        }
      }
    }`;

    const { data, errors } => await client.request(operation, {
        variables: {
            "lines": [{
                id: "gid://shopify/CartLine/${lineId}"
                quantity: quantity,
            }],
        }
    });

    if (errors) {
        console.warn(errors);

        return;
    }

    return data.cart;
};

export {
    addLines,
    cart,
    createFreshCart,
    getCartCount,
    getExistingCart,
    removeLine,
    updateLineQuantity,
};

export default cart;
