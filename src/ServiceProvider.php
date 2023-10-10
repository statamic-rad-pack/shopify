<?php

namespace StatamicRadPack\Shopify;

use Illuminate\Support\Facades\Artisan;
use Shopify\Auth\FileSessionStorage;
use Shopify\Auth\Session;
use Shopify\Clients\Graphql;
use Shopify\Clients\Rest;
use Shopify\Context;
use Statamic\Facades\Collection;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Facades\Taxonomy;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishAfterInstall = false;

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $fieldtypes = [
        \StatamicRadPack\Shopify\Fieldtypes\Variants::class,
        \StatamicRadPack\Shopify\Fieldtypes\DisabledText::class,
    ];

    protected $scripts = [
        __DIR__.'/../dist/js/statamic-shopify-cp.js',
    ];

    protected $tags = [
        \StatamicRadPack\Shopify\Tags\Shopify::class,
    ];

    protected $scopes = [
        \StatamicRadPack\Shopify\Scopes\VariantByProduct::class,
        \StatamicRadPack\Shopify\Scopes\VariantIsOnSale::class,
    ];

    protected $commands = [
        \StatamicRadPack\Shopify\Commands\ShopifyImportProducts::class,
        \StatamicRadPack\Shopify\Commands\ShopifyImportSingleProduct::class,
        \StatamicRadPack\Shopify\Commands\ShopifyImportCollections::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->createNavigation();

        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'shopify');
        $this->mergeConfigFrom(__DIR__.'/../config/shopify.php', 'shopify');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'shopify');

        Statamic::booted(function () {
            $this->setShopifyApiConfig();
            $this->publishAssets();
            $this->bootPermissions();
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/js/shopify' => resource_path('js/vendor/shopify'),
                __DIR__.'/../resources/js/front.js' => resource_path('js/vendor/shopify.js'),
            ], 'shopify-scripts');

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

            $this->publishes([
                __DIR__.'/../dist/js' => public_path('vendor/shopify/js'),
            ], 'shopify-resources');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/shopify'),
            ], 'shopify-theme');

            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/shopify'),
            ], 'shopify-translations');
        }
    }

    private function createNavigation(): void
    {
        $shopifySvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 109.5 124.5"><path d="M74.7 14.8s-1.4.4-3.7 1.1c-.4-1.3-1-2.8-1.8-4.4-2.6-5-6.5-7.7-11.1-7.7-.3 0-.6 0-1 .1-.1-.2-.3-.3-.4-.5-2-2.2-4.6-3.2-7.7-3.1-6 .2-12 4.5-16.8 12.2-3.4 5.4-6 12.2-6.7 17.5-6.9 2.1-11.7 3.6-11.8 3.7-3.5 1.1-3.6 1.2-4 4.5-.6 2.5-9.7 73-9.7 73l75.6 13.1V14.6c-.4.1-.7.1-.9.2zm-17.5 5.4c-4 1.2-8.4 2.6-12.7 3.9 1.2-4.7 3.6-9.4 6.4-12.5 1.1-1.1 2.6-2.4 4.3-3.2 1.7 3.6 2.1 8.5 2 11.8zM49.1 4.3c1.4 0 2.6.3 3.6.9-1.6.8-3.2 2.1-4.7 3.6-3.8 4.1-6.7 10.5-7.9 16.6-3.6 1.1-7.2 2.2-10.5 3.2 2.1-9.5 10.2-24 19.5-24.3zm-11.7 55c.4 6.4 17.3 7.8 18.3 22.9.7 11.9-6.3 20-16.4 20.6-12.2.8-18.9-6.4-18.9-6.4l2.6-11s6.7 5.1 12.1 4.7c3.5-.2 4.8-3.1 4.7-5.1-.5-8.4-14.3-7.9-15.2-21.7-.8-11.5 6.8-23.2 23.6-24.3 6.5-.4 9.8 1.2 9.8 1.2l-3.8 14.4s-4.3-2-9.4-1.6c-7.4.5-7.5 5.2-7.4 6.3zM61.2 19c0-3-.4-7.3-1.8-10.9 4.6.9 6.8 6 7.8 9.1-1.8.5-3.8 1.1-6 1.8zM78.1 123.9l31.4-7.8S96 24.8 95.9 24.2c-.1-.6-.6-1-1.1-1-.5 0-9.3-.2-9.3-.2s-5.4-5.2-7.4-7.2v108.1z"/></svg>';

        Nav::extend(function ($nav) use ($shopifySvg) {
            $collectionsNav = $nav->content('Collections');

            $children = $collectionsNav->children()()
                ->reject(function ($item) {
                    if (in_array($item->id(), ['::variants', '::products'])) {
                        return true;
                    }
                });

            $collectionsNav->children(function () use ($children) {
                    return $children;
                });

            $taxonomiesNav = $nav->content('Taxonomies');

            $children = $taxonomiesNav->children()()
                ->reject(function ($item) {
                    if (in_array($item->id(), ['::product_collections', '::product_tags', '::product_type', '::product_vendor'])) {
                        return true;
                    }
                });

            $taxonomiesNav->children(function () use ($children) {
                    return $children;
                });

            $nav->create(__('Shopify'))
                ->section('Shopify')
                ->icon($shopifySvg)
                ->route('collections.show', 'products')
                ->children([
                    $nav->create(__('Products'))
                        ->route('collections.show', 'products')
                        ->can('view', Collection::find('products')),

                    $nav->create(__('Collections'))
                        ->route('taxonomies.show', 'collections')
                        ->can('view', Taxonomy::find('collections')),

                    $nav->create(__('Tags'))
                        ->route('taxonomies.show', 'tags')
                        ->can('view', Taxonomy::find('tags')),

                    $nav->create(__('Product Types'))
                        ->route('taxonomies.show', 'type')
                        ->can('view', Taxonomy::find('type')),

                    $nav->create(__('Vendors'))
                        ->route('taxonomies.show', 'vendor')
                        ->can('view', Taxonomy::find('vendor')),

                    $nav->create(__('Settings'))
                        ->route('shopify.index')
                        ->can('access shopify'),
                ]);
        });
    }

    private function setShopifyApiConfig(): void
    {
        Context::initialize(
            apiKey: config('shopify.auth_key'),
            apiSecretKey: config('shopify.auth_password'),
            scopes: ['read_metaobjects', 'read_products'],
            hostName: config('shopify.url'),
            sessionStorage: new FileSessionStorage('/tmp/php_sessions'),
            apiVersion: '2023-04',
            isEmbeddedApp: false,
            isPrivateApp: true,
        );

        $this->app->bind(Rest::class, function ($app) {
            return new Rest(config('shopify.url'), config('shopify.admin_token'));
        });

        $this->app->bind(Graphql::class, function ($app) {
            return new Graphql(config('shopify.url'), config('shopify.admin_token'));
        });
    }

    private function publishAssets(): void
    {
        Statamic::afterInstalled(function () {
            Artisan::call('vendor:publish --tag=shopify-config');
            Artisan::call('vendor:publish --tag=shopify-blueprints');
            Artisan::call('vendor:publish --tag=shopify-content');
            Artisan::call('vendor:publish --tag=shopify-resources --force');
        });
    }

    private function bootPermissions()
    {
        $this->app->booted(function () {
            Permission::register('access shopify')->label('Manage Shopify Imports');
        });
    }
}
