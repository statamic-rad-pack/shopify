import { createStorefrontApiClient } from '@shopify/storefront-api-client';

/**
 * Set up a new version of the Shopify Buy this uses the
 * token values set by {{ shopify:tokens }} in your template.
 */
const client = createStorefrontApiClient({
    storeDomain: window.shopifyConfig.url,
    apiVersion: '2024-04',
    publicAccessToken: window.shopifyConfig.token,
});

export default client;
