<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Statamic\Console\RunsInPlease;

use function Laravel\Prompts\info;
use function Laravel\Prompts\multisearch;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class ShopifyInstall extends Command
{
    use RunsInPlease;

    protected $signature = 'shopify:install';

    protected $description = 'Install and authenticate with Shopify';

    protected $availableScopes = [
        'read_all_orders' => 'Read all orders',
        'read_assigned_fulfillment_orders' => 'Read assigned fulfillment orders',
        'read_cart_transforms' => 'Read cart transforms',
        'read_checkout_branding_settings' => 'Read checkout branding settings',
        'read_content' => 'Read content',
        'read_customer_events' => 'Read customer events',
        'read_customer_merge' => 'Read customer merge',
        'read_customer_payment_methods' => 'Read customer payment methods',
        'read_customers' => 'Read customers',
        'read_discounts' => 'Read discounts',
        'read_draft_orders' => 'Read draft orders',
        'read_files' => 'Read files',
        'read_fulfillments' => 'Read fulfillments',
        'read_gift_cards' => 'Read gift cards',
        'read_inventory' => 'Read inventory',
        'read_legal_policies' => 'Read legal policies',
        'read_locations' => 'Read locations',
        'read_marketing_events' => 'Read marketing events',
        'read_merchant_approval_signals' => 'Read merchant approval signals',
        'read_merchant_managed_fulfillment_orders' => 'Read merchant managed fulfillment orders',
        'read_metaobject_definitions' => 'Read metaobject definitions',
        'read_metaobjects' => 'Read metaobjects',
        'read_online_store_pages' => 'Read online store pages',
        'read_orders' => 'Read orders',
        'read_payment_terms' => 'Read payment terms',
        'read_price_rules' => 'Read price rules',
        'read_product_listings' => 'Read product listings',
        'read_products' => 'Read products',
        'read_publications' => 'Read publications',
        'read_reports' => 'Read reports',
        'read_return_policies' => 'Read return policies',
        'read_returns' => 'Read returns',
        'read_script_tags' => 'Read script tags',
        'read_shipping' => 'Read shipping',
        'read_shopify_payments_disputes' => 'Read Shopify payments disputes',
        'read_shopify_payments_payouts' => 'Read Shopify payments payouts',
        'read_themes' => 'Read themes',
        'read_third_party_fulfillment_orders' => 'Read third party fulfillment orders',
        'read_translations' => 'Read translations',
        'write_assigned_fulfillment_orders' => 'Write assigned fulfillment orders',
        'write_cart_transforms' => 'Write cart transforms',
        'write_checkout_branding_settings' => 'Write checkout branding settings',
        'write_content' => 'Write content',
        'write_customer_merge' => 'Write customer merge',
        'write_customers' => 'Write customers',
        'write_discounts' => 'Write discounts',
        'write_draft_orders' => 'Write draft orders',
        'write_files' => 'Write files',
        'write_fulfillments' => 'Write fulfillments',
        'write_gift_cards' => 'Write gift cards',
        'write_inventory' => 'Write inventory',
        'write_marketing_events' => 'Write marketing events',
        'write_merchant_managed_fulfillment_orders' => 'Write merchant managed fulfillment orders',
        'write_metaobject_definitions' => 'Write metaobject definitions',
        'write_metaobjects' => 'Write metaobjects',
        'write_online_store_pages' => 'Write online store pages',
        'write_orders' => 'Write orders',
        'write_payment_terms' => 'Write payment terms',
        'write_pixels' => 'Write pixels',
        'write_price_rules' => 'Write price rules',
        'write_products' => 'Write products',
        'write_publications' => 'Write publications',
        'write_reports' => 'Write reports',
        'write_return_policies' => 'Write return policies',
        'write_returns' => 'Write returns',
        'write_script_tags' => 'Write script tags',
        'write_shipping' => 'Write shipping',
        'write_themes' => 'Write themes',
        'write_third_party_fulfillment_orders' => 'Write third party fulfillment orders',
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
        info('================================================================');
        info('==================== SHOPIFY INSTALLATION ======================');
        info('================================================================');

        // Get client credentials
        $clientId = text(
            label: 'Enter your Shopify Client ID',
            required: true
        );

        $clientSecret = password(
            label: 'Enter your Shopify Client Secret',
            required: true
        );

        // Select scopes
        $selectedScopes = $this->selectScopes();
        $scope = implode(',', $selectedScopes);

        // Request authorization
        $shopifyUrl = config('shopify.url');
        $redirectUrl = config('app.url') . '/admin/shopify/callback';
        $state = uniqid();

        info('Requesting authorization...');

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
        info('Exchanging authorization code for access token...');

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
        info('Writing token to config file...');
        $this->updateConfigFile($token);

        info('✓ Successfully authenticated with Shopify!');
        info('The admin token has been written to config/shopify.php');

        return 0;
    }

    protected function selectScopes()
    {
        $selected = multisearch(
            label: 'Select the API scopes you need',
            placeholder: 'Search for scopes...',
            options: fn (string $value) => strlen($value) > 0
                ? collect($this->availableScopes)
                    ->filter(fn ($description, $scope) =>
                        str_contains(strtolower($scope), strtolower($value)) ||
                        str_contains(strtolower($description), strtolower($value))
                    )
                    ->mapWithKeys(fn ($description, $scope) => [$scope => "{$scope} - {$description}"])
                    ->all()
                : collect($this->availableScopes)
                    ->mapWithKeys(fn ($description, $scope) => [$scope => "{$scope} - {$description}"])
                    ->all(),
            default: $this->defaultScopes,
            required: true,
            scroll: 10
        );

        return $selected;
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
