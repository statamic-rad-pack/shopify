import Client from 'shopify-buy';

console.log(window.shopifyDomain)
console.log(window.shopifyToken)

const client = Client.buildClient({
    domain: window.shopifyDomain,
    storefrontAccessToken: window.shopifyToken
});

console.log(client);
