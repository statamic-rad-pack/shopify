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
     * Whether the importer should overwrite the content stored.
     */
    'overwrite' => [
        'title' => true,
        'content' => true,
        'vendor' => true,
        'type' => true,
        'tags' => true
    ]
];
