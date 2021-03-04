<?php

namespace Jackabox\Shopify;

use Statamic\Facades\Collection;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use PHPShopify\ShopifySDK;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
        'actions' => __DIR__.'/../routes/actions.php',
    ];

    protected $fieldtypes = [
        \Jackabox\Shopify\Fieldtypes\Variants::class,
        \Jackabox\Shopify\Fieldtypes\DisabledText::class,
    ];

    protected $scripts = [
        __DIR__.'/../dist/js/statamic-shopify-cp.js',
    ];

    protected $tags = [
        \Jackabox\Shopify\Tags\ShopifyScripts::class
    ];

    protected $scopes = [
        \Jackabox\Shopify\Scopes\VariantByProduct::class
    ];

    protected $commands = [
        \Jackabox\Shopify\Commands\ShopifyImportProducts::class,
        \Jackabox\Shopify\Commands\ShopifyImportSingleProduct::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->createNavigation();

        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'shopify');
        $this->mergeConfigFrom(__DIR__ . '/../config/shopify.php', 'shopify');

        Statamic::booted(function() {
            $this->setShopifyApiConfig();
            $this->publishAssets();
            $this->bootPermissions();
        });


        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../dist/js/statamic-shopify-front.js' => public_path('vendor/shopify/js/statamic-shopify-front.js'),
            ], 'shopify-assets');

            $this->publishes([
                __DIR__.'/../content/assets' => base_path('content/assets'),
                __DIR__.'/../content/collections' => base_path('content/collections'),
                __DIR__.'/../content/taxonomies' => base_path('content/taxonomies'),
            ], 'shopify-content');

            $this->publishes([
                __DIR__.'/../resources/blueprints' => resource_path('blueprints'),
            ], 'shopify-blueprints');

            $this->publishes([
                __DIR__.'/../config/shopify.php' => config_path('shopify.php'),
            ], 'shopify-config');
        }
    }

    private function createNavigation(): void
    {
        Nav::extend(function ($nav) {
            $nav->create('Settings')
                ->icon('settings-slider')
                ->section('Shopify')
                ->can(auth()->user()->can('access shopify'))
                ->route('shopify.index');

            // Hide the variants from the nav bar.
            // TODO: Is there a way to hide this all together?
            $nav->content('Collections')
                ->children(function () {
                    return Collection::all()->sortBy->title()->filter(function ($item) {
                        return $item->handle() !== 'variants';
                    })->map(function ($collection) {
                        return Nav::item($collection->title())
                            ->url($collection->showUrl())
                            ->can('view', $collection);
                    });
                });
        });
    }

    private function setShopifyApiConfig(): void
    {
        $config = [
            'ShopUrl' => config('shopify.url'),
            'ApiKey' => config('shopify.auth_key'),
            'Password' => config('shopify.auth_password'),
        ];

        ShopifySDK::config($config);
    }

    private function publishAssets(): void
    {
        Statamic::afterInstalled(function () {
            Artisan::call('vendor:publish --tag=shopify-config');
            Artisan::call('vendor:publish --tag=shopify-blueprints');
            Artisan::call('vendor:publish --tag=shopify-collections');
        });
    }

    private function bootPermissions()
    {
        $this->app->booted(function () {
            Permission::register('access shopify')->label('Manage Shopify Imports');
        });
    }
}
