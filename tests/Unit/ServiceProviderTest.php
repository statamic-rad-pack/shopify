<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\ServiceProvider;
use StatamicRadPack\Shopify\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    #[Test]
    public function installs_collections_assets_and_taxonomies()
    {
        ServiceProvider::installCollectionsTaxonomiesAssetsAndBlueprints();

        $this->assertNotNull(Facades\Collection::find('products'));
        $this->assertNotNull(Facades\Collection::find('variants'));
        $this->assertNotNull(Facades\Taxonomy::find('collections'));
        $this->assertNotNull(Facades\Taxonomy::find('tags'));
        $this->assertNotNull(Facades\Taxonomy::find('type'));
        $this->assertNotNull(Facades\Taxonomy::find('vendor'));
        $this->assertNotNull(Facades\AssetContainer::find('shopify'));
    }
}
