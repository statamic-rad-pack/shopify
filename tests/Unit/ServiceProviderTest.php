<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\ServiceProvider;
use StatamicRadPack\Shopify\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    #[Test]
    public function installs_collections_assets_and_taxonomies()
    {
        ServiceProvider::installCollectionsTaxonomiesAssetsAndBlueprints();

        $this->assertNotNull(Facades\Collection::find(config('shopify.collection_handle', 'products')));
        $this->assertNotNull(Facades\Collection::find('variants'));
        $this->assertNotNull(Facades\Taxonomy::find('collections'));
        $this->assertNotNull(Facades\Taxonomy::find('tags'));
        $this->assertNotNull(Facades\Taxonomy::find('type'));
        $this->assertNotNull(Facades\Taxonomy::find('vendor'));
        $this->assertNotNull(Facades\AssetContainer::find('shopify'));
    }

    #[Test]
    public function uses_a_legacy_admin_token_from_the_environment()
    {
        // phpunit.xml sets SHOPIFY_ADMIN_TOKEN, with no client id or secret
        $this->assertSame('admin-token', config('shopify.admin_token'));

        $client = $this->app->make(Graphql::class);
        $token = (fn () => $this->token)->call($client);

        $this->assertSame('admin-token', $token);
    }
}
