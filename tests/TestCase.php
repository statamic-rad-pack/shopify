<?php

namespace StatamicRadPack\Shopify\Tests;

use JMac\Testing\Traits\AdditionalAssertions;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Facades\Blueprint as BlueprintFacade;
use Statamic\Facades\YAML;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;
use StatamicRadPack\Shopify\ServiceProvider;

class TestCase extends OrchestraTestCase
{
    use AdditionalAssertions, PreventSavingStacheItemsToDisk;

    public function setup(): void
    {
        parent::setup();
        $this->preventSavingStacheItemsToDisk();
    }

    public function tearDown(): void
    {
        $this->deleteFakeStacheDirectory();

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [StatamicServiceProvider::class, ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'statamic-rad-pack/shopify' => [
                'id' => 'statamic-rad-pack/shopify',
                'namespace' => 'StatamicRadPack\\Shopify',
            ],
        ];

        config(['statamic.users.repository' => 'file']);
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = ['assets', 'cp', 'forms', 'routes', 'static_caching', 'sites', 'stache', 'system', 'users'];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require __DIR__."/../vendor/statamic/cms/config/{$config}.php");
        }

        // Setting the user repository to the default flat file system
        $app['config']->set('statamic.users.repository', 'file');

        // Assume the pro edition within tests
        $app['config']->set('statamic.editions.pro', true);

        Statamic::booted(function () {
            $blueprintContents = YAML::parse(file_get_contents(__DIR__.'/../resources/blueprints/collections/products/product.yaml'));

            $productCollectionBlueprint = BlueprintFacade::make()
                ->setNamespace('collections.products')
                ->setHandle('product')
                ->setContents($blueprintContents)
                ->save();

            $blueprintContents = YAML::parse(file_get_contents(__DIR__.'/../resources/blueprints/collections/variants/variant.yaml'));

            $variantCollectionBlueprint = BlueprintFacade::make()
                ->setNamespace('collections.variants')
                ->setHandle('variant')
                ->setContents($blueprintContents)
                ->save();
        });
    }
}
