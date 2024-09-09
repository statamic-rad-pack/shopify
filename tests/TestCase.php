<?php

namespace StatamicRadPack\Shopify\Tests;

use JMac\Testing\Traits\AdditionalAssertions;
use Statamic\Facades;
use Statamic\Statamic;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use StatamicRadPack\Shopify\ServiceProvider;

class TestCase extends AddonTestCase
{
    use AdditionalAssertions, PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected $shouldFakeVersion = true;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Setting the user repository to the default flat file system
        $app['config']->set('statamic.users.repository', 'file');

        // Assume the pro edition within tests
        $app['config']->set('statamic.editions.pro', true);

        $app['config']->set('statamic.system.multisite', true);

        Statamic::booted(function () {
            $blueprintContents = Facades\YAML::parse(file_get_contents(__DIR__.'/../resources/blueprints/collections/products/product.yaml'));

            $productCollectionBlueprint = Facades\Blueprint::make()
                ->setNamespace('collections.products')
                ->setHandle('product')
                ->setContents($blueprintContents)
                ->save();

            Facades\Collection::make('products')
                ->save();

            $blueprintContents = Facades\YAML::parse(file_get_contents(__DIR__.'/../resources/blueprints/collections/variants/variant.yaml'));

            $variantCollectionBlueprint = Facades\Blueprint::make()
                ->setNamespace('collections.variants')
                ->setHandle('variant')
                ->setContents($blueprintContents)
                ->save();

            Facades\Collection::make('variants')
                ->save();
        });
    }
}
