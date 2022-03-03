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
     * Admin Access Token
     */
    'admin_token' => env('SHOPIFY_ADMIN_TOKEN'),

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
     * Asset Container to Store the imported Assets
     */
    'asset' => [
        'path' => env('SHOPIFY_ASSET_PATH', 'shopify'),
        'container' => env('SHOPIFY_ASSET_CONTAINER', 'shopify'),
    ],

    /**
     * If you've renamed the taxonomies in your admin panel, you
     * can update these values so everything is kept in sync
     */
    'taxonomies' => [
        'type' => env('SHOPIFY_TAXONOMY_TYPE', 'type'),
        'tags' => env('SHOPIFY_TAXONOMY_TAGS', 'tags'),
        'vendor' => env('SHOPIFY_TAXONOMY_VENDOR', 'vendor'),
        'collections' => env('SHOPIFY_TAXONOMY_COLLECTIONS', 'collections'),
    ],

    /**
     * The queue connection you want the Shopify jobs to run on.
     * Please note you having more than 1 process running at once on this queue may cause issues.
     */
    'queue' => env('SHOPIFY_JOB_QUEUE', 'default')
];
