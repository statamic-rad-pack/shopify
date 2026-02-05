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

        // Request authorization
        $shopifyUrl = config('shopify.url');
        $redirectUrl = config('app.url') . '/admin/shopify/callback';
        $state = uniqid();
        $scope = 'write_customers,read_inventory,read_metaobjects,read_orders,read_product_listings,read_products,read_publications,read_translations';

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
