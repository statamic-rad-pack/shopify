<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Statamic\Console\RunsInPlease;

class ShopifyInstall extends Command
{
    use RunsInPlease;

    protected $signature = 'shopify:install';

    protected $description = 'Install and authenticate with Shopify';

    protected $availableScopes = [
        'read_all_orders' => 'Read all orders',
        'read_assigned_fulfillment_orders' => 'Read assigned fulfillment orders',
        'write_assigned_fulfillment_orders' => 'Write assigned fulfillment orders',
        'read_merchant_managed_fulfillment_orders' => 'Read merchant managed fulfillment orders',
        'write_merchant_managed_fulfillment_orders' => 'Write merchant managed fulfillment orders',
        'read_third_party_fulfillment_orders' => 'Read third party fulfillment orders',
        'write_third_party_fulfillment_orders' => 'Write third party fulfillment orders',
        'read_cart_transforms' => 'Read cart transforms',
        'write_cart_transforms' => 'Write cart transforms',
        'read_checkout_branding_settings' => 'Read checkout branding settings',
        'write_checkout_branding_settings' => 'Write checkout branding settings',
        'read_content' => 'Read content',
        'write_content' => 'Write content',
        'read_customer_events' => 'Read customer events',
        'write_pixels' => 'Write pixels',
        'read_customer_merge' => 'Read customer merge',
        'write_customer_merge' => 'Write customer merge',
        'read_customer_payment_methods' => 'Read customer payment methods',
        'read_customers' => 'Read customers',
        'write_customers' => 'Write customers',
        'read_discounts' => 'Read discounts',
        'write_discounts' => 'Write discounts',
        'read_draft_orders' => 'Read draft orders',
        'write_draft_orders' => 'Write draft orders',
        'read_files' => 'Read files',
        'write_files' => 'Write files',
        'read_fulfillments' => 'Read fulfillments',
        'write_fulfillments' => 'Write fulfillments',
        'read_gift_cards' => 'Read gift cards',
        'write_gift_cards' => 'Write gift cards',
        'read_inventory' => 'Read inventory',
        'write_inventory' => 'Write inventory',
        'read_legal_policies' => 'Read legal policies',
        'read_locations' => 'Read locations',
        'read_marketing_events' => 'Read marketing events',
        'write_marketing_events' => 'Write marketing events',
        'read_merchant_approval_signals' => 'Read merchant approval signals',
        'read_metaobject_definitions' => 'Read metaobject definitions',
        'write_metaobject_definitions' => 'Write metaobject definitions',
        'read_metaobjects' => 'Read metaobjects',
        'write_metaobjects' => 'Write metaobjects',
        'read_online_store_pages' => 'Read online store pages',
        'write_online_store_pages' => 'Write online store pages',
        'read_orders' => 'Read orders',
        'write_orders' => 'Write orders',
        'read_payment_terms' => 'Read payment terms',
        'write_payment_terms' => 'Write payment terms',
        'read_price_rules' => 'Read price rules',
        'write_price_rules' => 'Write price rules',
        'read_product_listings' => 'Read product listings',
        'read_products' => 'Read products',
        'write_products' => 'Write products',
        'read_publications' => 'Read publications',
        'write_publications' => 'Write publications',
        'read_reports' => 'Read reports',
        'write_reports' => 'Write reports',
        'read_return_policies' => 'Read return policies',
        'write_return_policies' => 'Write return policies',
        'read_returns' => 'Read returns',
        'write_returns' => 'Write returns',
        'read_script_tags' => 'Read script tags',
        'write_script_tags' => 'Write script tags',
        'read_shipping' => 'Read shipping',
        'write_shipping' => 'Write shipping',
        'read_shopify_payments_disputes' => 'Read Shopify payments disputes',
        'read_shopify_payments_payouts' => 'Read Shopify payments payouts',
        'read_themes' => 'Read themes',
        'write_themes' => 'Write themes',
        'read_translations' => 'Read translations',
        'write_translations' => 'Write translations',
    ];

    protected $defaultScopes = [
        'write_customers',
        'read_inventory',
        'read_metaobjects',
        'read_orders',
        'read_product_listings',
        'read_products',
        'read_publications',
        'read_translations',
    ];

    public function handle()
    {
        $this->info('================================================================');
        $this->info('==================== SHOPIFY INSTALLATION ======================');
        $this->info('================================================================');

        // Get client credentials
        $clientId = $this->ask('Enter your Shopify Client ID');
        $clientSecret = $this->secret('Enter your Shopify Client Secret');

        if (empty($clientId) || empty($clientSecret)) {
            $this->error('Client ID and Client Secret are required.');
            return 1;
        }

        // Select scopes
        $selectedScopes = $this->selectScopes();
        $scope = implode(',', $selectedScopes);

        // Request authorization
        $shopifyUrl = config('shopify.url');
        $redirectUrl = config('app.url') . '/admin/shopify/callback';
        $state = uniqid();

        $this->info('Requesting authorization...');

        $authResponse = Http::post($shopifyUrl . '/admin/oauth/authorize', [
            'client_id' => $clientId,
            'scope' => $scope,
            'redirect_uri' => $redirectUrl,
            'state' => $state,
            'grant_options' => 'offline',
        ]);

        if (!$authResponse->successful()) {
            $this->error('Authorization request failed: ' . $authResponse->body());
            return 1;
        }

        $code = $authResponse->json('code');

        if (empty($code)) {
            $this->error('No authorization code received from Shopify.');
            return 1;
        }

        // Exchange code for access token
        $this->info('Exchanging authorization code for access token...');

        $tokenResponse = Http::post($shopifyUrl . '/admin/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
        ]);

        if (!$tokenResponse->successful()) {
            $this->error('Failed to obtain access token: ' . $tokenResponse->body());
            return 1;
        }

        $token = $tokenResponse->json('access_token');

        if (empty($token)) {
            $this->error('No access token received from Shopify.');
            return 1;
        }

        // Update the config file
        $this->info('Writing token to config file...');
        $this->updateConfigFile($token);

        $this->info('✓ Successfully authenticated with Shopify!');
        $this->info('The admin token has been written to config/shopify.php');

        return 0;
    }

    protected function selectScopes()
    {
        $this->info('Select the API scopes you need (press space to toggle, enter to confirm):');

        $choices = [];
        foreach ($this->availableScopes as $scope => $description) {
            $choices[] = $scope;
        }

        $selected = $this->choice(
            'Which scopes do you need?',
            $choices,
            implode(',', array_keys(array_flip($this->defaultScopes))),
            null,
            true
        );

        return is_array($selected) ? $selected : [$selected];
    }

    protected function updateConfigFile($token)
    {
        $configPath = config_path('shopify.php');
        $content = file_get_contents($configPath);

        // Update the admin_token line
        $content = preg_replace(
            "/'admin_token' => env\('SHOPIFY_ADMIN_TOKEN'\),/",
            "'admin_token' => env('SHOPIFY_ADMIN_TOKEN', '{$token}'),",
            $content
        );

        file_put_contents($configPath, $content);
    }
}
