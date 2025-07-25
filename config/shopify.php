<?php

return [
    /**
     * Site URL
     */
    'url' => env('SHOPIFY_APP_URL'),

    /**
     * If you use a different customer facing url (eg store.mycustomdomain.com)
     * please enter that here
     */
    'storefront_url' => env('SHOPIFY_STOREFRONT_URL'),

    /**
     * Front-end storefront token
     */
    'storefront_token' => env('SHOPIFY_STOREFRONT_TOKEN'),

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
        'tags' => true,
    ],

    /**
     * Admin API version - Set this to a fixed value (eg 2023-07) or null to let the library decide
     */
    'api_version' => env('SHOPIFY_API_VERSION', '2025-04'),

    /**
     * Admin connection is a private app, defaults to false
     */
    'api_private_app' => false,

    /**
     * Shop Currency
     */
    'currency' => '£',

    /**
     * Change some of the language variables used in the tags
     */
    'lang' => [
        'out_of_stock' => 'Out of Stock',
        'from' => 'From',
    ],

    /**
     * Asset Container to Store the imported Assets
     */
    'asset' => [
        'path' => env('SHOPIFY_ASSET_PATH', ''),
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
    'queue' => env('SHOPIFY_JOB_QUEUE', 'default'),

    /**
     * What class should we use to parse metafields
     */
    'metafields_parser' => \StatamicRadPack\Shopify\Actions\ParseMetafields::class,

    /**
     * If a new user is created by Shopify should the website create
     * a matching user account for them?
     */
    'create_users_from_shopify' => false,

    /**
     * If a user is created or modified in Statamic, should we create/update that user in Shopify?
     */
    'update_users_in_shopify' => false,

    /**
     * What job should we use to update users in Shopify
     */
    'update_shopify_user_job' => \StatamicRadPack\Shopify\Jobs\CreateOrUpdateShopifyUser::class,

    /**
     * Where should the Shopify API client store its session data
     */
    'session_storage_path' => env('SHOPIFY_SESSION_STORAGE_PATH', '/tmp/php_sessions'),

    /**
     * In which collection should the Shopify products be created
     */
    'collection_handle' => env('SHOPIFY_COLLECTION_HANDLE', 'products'),

    /**
     * What Sales Channel in Shopify should be used to determine the product availablity
     * (ensure this matches exactly)
     */
    'sales_channel' => env('SHOPIFY_SALES_CHANNEL', 'Online Store'),

    /**
     * Your App's Admin API Auth Key
     * Note: this is not required unless you are doing custom integrations using the RestApi
     */
    'auth_key' => env('SHOPIFY_AUTH_KEY', 'api-key'),

    /**
     * Admin API Auth Password
     * Note: this is not required unless you are doing custom integrations using the RestApi
     */
    'auth_password' => env('SHOPIFY_AUTH_PASSWORD', 'api-password'),
];
