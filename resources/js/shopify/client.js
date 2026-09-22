import { createStorefrontApiClient } from '@shopify/storefront-api-client';

let instance = null;

/**
 * Set up a new version of the Shopify Buy this uses the
 * token values set by {{ shopify:tokens }} in your template.
 *
 * The client is created on first use so that importing this module does not
 * throw on pages that do not output {{ shopify:tokens }}.
 */
const client = {
    request(...args) {
        if (! instance) {
            instance = createStorefrontApiClient({
                storeDomain: window.shopifyConfig.url,
                apiVersion: window.shopifyConfig.apiVersion ?? '2024-07',
                publicAccessToken: window.shopifyConfig.token,
            });
        }

        return instance.request(...args);
    },
};

export default client;
