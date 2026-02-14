<?php

namespace StatamicRadPack\Shopify;

use Illuminate\Support\Facades\Artisan;
use Shopify\Auth\FileSessionStorage;
use Shopify\Clients\Graphql;
use Shopify\Context;
use Statamic\Events;
use Statamic\Facades;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        Commands\ShopifyImportProducts::class,
        Commands\ShopifyImportSingleProduct::class,
        Commands\ShopifyImportCollections::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\Variants::class,
        Fieldtypes\DisabledText::class,
    ];

    protected $listen = [
        Events\UserSaved::class => [Listeners\UserSavedListener::class],
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $scopes = [
        Scopes\VariantByProduct::class,
        Scopes\VariantIsOnSale::class,
    ];

    protected $tags = [
        Tags\Shopify::class,
    ];

    protected $vite = [
        'publicDirectory' => 'dist',
        'hotFile' => 'vendor/shopify/hot',
        'input' => [
            'resources/js/cp.js',
        ],
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
            ], 'shopify-scripts');

            $this->publishes([
                __DIR__.'/../config/shopify.php' => config_path('shopify.php'),
            ], 'shopify-config');

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
        $shopifySvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 109.5 124.5"><path fill="currentColor" d="M74.7 14.8s-1.4.4-3.7 1.1c-.4-1.3-1-2.8-1.8-4.4-2.6-5-6.5-7.7-11.1-7.7-.3 0-.6 0-1 .1-.1-.2-.3-.3-.4-.5-2-2.2-4.6-3.2-7.7-3.1-6 .2-12 4.5-16.8 12.2-3.4 5.4-6 12.2-6.7 17.5-6.9 2.1-11.7 3.6-11.8 3.7-3.5 1.1-3.6 1.2-4 4.5-.6 2.5-9.7 73-9.7 73l75.6 13.1V14.6c-.4.1-.7.1-.9.2zm-17.5 5.4c-4 1.2-8.4 2.6-12.7 3.9 1.2-4.7 3.6-9.4 6.4-12.5 1.1-1.1 2.6-2.4 4.3-3.2 1.7 3.6 2.1 8.5 2 11.8zM49.1 4.3c1.4 0 2.6.3 3.6.9-1.6.8-3.2 2.1-4.7 3.6-3.8 4.1-6.7 10.5-7.9 16.6-3.6 1.1-7.2 2.2-10.5 3.2 2.1-9.5 10.2-24 19.5-24.3zm-11.7 55c.4 6.4 17.3 7.8 18.3 22.9.7 11.9-6.3 20-16.4 20.6-12.2.8-18.9-6.4-18.9-6.4l2.6-11s6.7 5.1 12.1 4.7c3.5-.2 4.8-3.1 4.7-5.1-.5-8.4-14.3-7.9-15.2-21.7-.8-11.5 6.8-23.2 23.6-24.3 6.5-.4 9.8 1.2 9.8 1.2l-3.8 14.4s-4.3-2-9.4-1.6c-7.4.5-7.5 5.2-7.4 6.3zM61.2 19c0-3-.4-7.3-1.8-10.9 4.6.9 6.8 6 7.8 9.1-1.8.5-3.8 1.1-6 1.8zM78.1 123.9l31.4-7.8S96 24.8 95.9 24.2c-.1-.6-.6-1-1.1-1-.5 0-9.3-.2-9.3-.2s-5.4-5.2-7.4-7.2v108.1z"/></svg>';

        Facades\CP\Nav::extend(function ($nav) use ($shopifySvg) {
            $collections = [
                config('shopify.collection_handle', 'products'),
                'variants',
            ];
            $taxonomies = [
                config('shopify.taxonomies.collections'),
                config('shopify.taxonomies.tags'),
                config('shopify.taxonomies.type'),
                config('shopify.taxonomies.vendor'),
            ];
            foreach ($collections as $handle) {
                if ($collection = Facades\Collection::find($handle)) {
                    $nav->remove('Content', 'Collections', $collection->title());
                }
            }
            foreach ($taxonomies as $handle) {
                if ($taxonomy = Facades\Taxonomy::find($handle)) {
                    $nav->remove('Content', 'Taxonomies', $taxonomy->title());
                }
            }

            $user = Facades\User::current();

            $canViewSomething = $user->can('view', Facades\Collection::find(config('shopify.collection_handle', 'products')))
                || $user->can('view', Facades\Taxonomy::find(config('shopify.taxonomies.collections')))
                || $user->can('view', Facades\Taxonomy::find(config('shopify.taxonomies.tags')))
                || $user->can('view', Facades\Taxonomy::find(config('shopify.taxonomies.type')))
                || $user->can('view', Facades\Taxonomy::find(config('shopify.taxonomies.vendor')))
                || $user->can('access shopify');

            if (! $canViewSomething) {
                return;
            }

            $nav->create(__('Shopify'))
                ->section('Shopify')
                ->icon($shopifySvg)
                ->route('collections.show', config('shopify.collection_handle', 'products'))
                ->children(function () use ($nav) {
                    return [
                        $nav->create(__('Products'))
                            ->route('collections.show', config('shopify.collection_handle', 'products'))
                            ->can('view', Facades\Collection::find(config('shopify.collection_handle', 'products'))),

                        $nav->create(__('Collections'))
                            ->route('taxonomies.show', config('shopify.taxonomies.collections'))
                            ->can('view', Facades\Taxonomy::find(config('shopify.taxonomies.collections'))),

                        $nav->create(__('Tags'))
                            ->route('taxonomies.show', config('shopify.taxonomies.tags'))
                            ->can('view', Facades\Taxonomy::find(config('shopify.taxonomies.tags'))),

                        $nav->create(__('Product Types'))
                            ->route('taxonomies.show', config('shopify.taxonomies.type'))
                            ->can('view', Facades\Taxonomy::find(config('shopify.taxonomies.type'))),

                        $nav->create(__('Vendors'))
                            ->route('taxonomies.show', config('shopify.taxonomies.vendor'))
                            ->can('view', Facades\Taxonomy::find(config('shopify.taxonomies.vendor'))),

                        $nav->create(__('Settings'))
                            ->route('shopify.index')
                            ->can('access shopify'),
                    ];
                });
        });
    }

    private function setShopifyApiConfig(): void
    {
        if (! $key = config('shopify.admin_token')) {
            return;
        }

        Context::initialize(
            apiKey: config('shopify.auth_key'),
            apiSecretKey: config('shopify.auth_password'),
            scopes: ['read_metaobjects', 'read_products'],
            hostName: config('shopify.url'),
            sessionStorage: new FileSessionStorage(config('shopify.session_storage_path', '/tmp/php_sessions')),
            apiVersion: config('shopify.api_version'),
            isEmbeddedApp: false,
            isPrivateApp: config('shopify.api_private_app') ?? false,
        );

        $this->app->bind(Graphql::class, function ($app) {
            return new Graphql(config('shopify.url'), config('shopify.admin_token'));
        });
    }

    private function publishAssets(): void
    {
        Statamic::afterInstalled(function () {
            Artisan::call('vendor:publish --tag=shopify-config');
            Artisan::call('vendor:publish --tag=shopify-resources --force');

            static::installCollectionsTaxonomiesAssetsAndBlueprints();
        });
    }

    private function bootPermissions()
    {
        $this->app->booted(function () {
            Facades\Permission::register('access shopify')->label('Manage Shopify Imports');
        });
    }

    public static function installCollectionsTaxonomiesAssetsAndBlueprints()
    {
        if (! Facades\AssetContainer::find(config('shopify.asset.container'))) {
            Facades\AssetContainer::make()
                ->handle(config('shopify.asset.container'))
                ->title('Shopify')
                ->disk('assets')
                ->save();
        }

        if (! Facades\Taxonomy::find(config('shopify.taxonomies.collections'))) {
            $taxonomy = tap(Facades\Taxonomy::make()
                ->handle(config('shopify.taxonomies.collections'))
                ->title('Product Collections'))
                ->save();

            $taxonomy->termBlueprint()
                ->setContents(Facades\YAML::file(__DIR__.'/../resources/blueprints/collection.yaml')->parse())
                ->save();
        }

        if (! Facades\Taxonomy::find(config('shopify.taxonomies.tags'))) {
            Facades\Taxonomy::make()
                ->handle(config('shopify.taxonomies.tags'))
                ->title('Product Tags')
                ->save();
        }

        if (! Facades\Taxonomy::find(config('shopify.taxonomies.type'))) {
            Facades\Taxonomy::make()
                ->handle(config('shopify.taxonomies.type'))
                ->title('Product Type')
                ->save();
        }

        if (! Facades\Taxonomy::find(config('shopify.taxonomies.vendor'))) {
            Facades\Taxonomy::make()
                ->handle(config('shopify.taxonomies.vendor'))
                ->title('Product Vendor')
                ->save();
        }

        if (! Facades\Collection::find(config('shopify.collection_handle', 'products'))) {
            $collection = tap(Facades\Collection::make()
                ->handle(config('shopify.collection_handle', 'products'))
                ->title('Products')
                ->routes('/products/{slug}')
                ->template('product')
                ->dated(true)
                ->taxonomies(collect(config('shopify.taxonomies', []))->values()->all()))
                ->save();

            $collection->entryBlueprint()
                ->setContents(Facades\YAML::file(__DIR__.'/../resources/blueprints/product.yaml')->parse())
                ->save();
        }

        if (! Facades\Collection::find('variants')) {
            $collection = tap(Facades\Collection::make()
                ->handle('variants')
                ->title('Variants'))
                ->save();

            $collection->entryBlueprint()
                ->setContents(Facades\YAML::file(__DIR__.'/../resources/blueprints/variant.yaml')->parse())
                ->save();
        }
    }
}
