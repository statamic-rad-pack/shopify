import Client from 'shopify-buy'

/**
 * Set up a new version of the Shopify Buy this uses the
 * token values set by {{ shopify:tokens }} in your template.
 */
const client = Client.buildClient({
  domain: window.shopifyUrl,
  storefrontAccessToken: window.shopifyToken
})

export default client
