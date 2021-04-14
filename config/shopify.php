<?php

return [
    /**
     * Site URL
     */
    'url' => env('SHOPIFY_APP_URL'),

    /**
     * Front-end storefront token
     */
    'storefront_token' => env('SHOPIFY_STOREFRONT_TOKEN'),

    /**
     * Admin API Auth Key
     */
    'auth_key' => env('SHOPIFY_AUTH_KEY'),

    /**
     * Admin API Auth Password
     */
    'auth_password' => env('SHOPIFY_AUTH_PASSWORD'),

    /**
     * If using WebHooks make sure you have added the secret that they are signed with here
     */
    'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET'),

    /**
     * Whether the importer should overwrite the content stored.
     */
    'overwrite' => [
        'title' => true,
        'content' => true,
        'vendor' => true,
        'type' => true,
        'tags' => true
    ],

    /**
     * Admin API Limit - Lower this if you bump into issues.
     */
    'api_limit' => 30,

    /**
     * Shop Currency
     */
    'currency' => '£',

    /**
     * Change some of the language variables used in the tags
     */
    'lang' => [
        'out_of_stock' => 'Out of Stock',
        'from' => 'From'
    ],

    /**
     * If you've renamed the taxonomies in your admin panel, you
     * can update these values so everything is kept in sync
     */
    'taxonomies' => [
        'type' => env('SHOPIFY_TAXONOMY_TYPE', 'product_type'),
        'tags' => env('SHOPIFY_TAXONOMY_TAGS', 'product_tags'),
        'vendor' => env('SHOPIFY_TAXONOMY_VENDOR', 'vendor'),
    ]
];
