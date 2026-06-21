<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\User;
use StatamicRadPack\Shopify\Tests\TestCase;

class VariantsControllerTest extends TestCase
{
    private function actingAsSuperUser()
    {
        $user = User::make()->email('admin@example.com')->makeSuper()->save();

        return $this->actingAs($user);
    }

    private function setupMultisite(): void
    {
        Facades\Site::setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'de' => ['url' => '/de/', 'locale' => 'de_DE'],
        ]);

        Facades\Collection::make(config('shopify.collection_handle', 'products'))->sites(['en', 'de'])->save();
        Facades\Collection::make('variants')->sites(['en', 'de'])->save();
    }

    private function makeVariant(string $slug, array $data = [])
    {
        $variant = Facades\Entry::make()
            ->collection('variants')
            ->slug($slug)
            ->data(array_merge(['product_slug' => 'test-product'], $data));

        $variant->save();

        return $variant;
    }

    #[Test]
    public function returns_variants_for_default_site()
    {
        $this->makeVariant('variant-en', ['title' => 'English Variant', 'price' => '10.00', 'sku' => 'EN-1']);

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.variants.index', 'test-product'));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.title', 'English Variant');
    }

    #[Test]
    public function filters_variants_by_selected_site()
    {
        $this->setupMultisite();

        $enVariant = $this->makeVariant('variant-en', ['title' => 'English Variant', 'price' => '10.00', 'sku' => 'EN-1']);
        $enVariant->makeLocalization('de')->data(['title' => 'German Variant', 'price' => '12.00', 'sku' => 'DE-1'])->save();

        Facades\Site::setSelected('en');

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.variants.index', 'test-product'));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.title', 'English Variant');

        Facades\Site::setSelected('de');

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.variants.index', 'test-product'));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.title', 'German Variant');
    }

    #[Test]
    public function localized_variant_inherits_title_price_and_sku_from_origin()
    {
        $this->setupMultisite();

        $enVariant = $this->makeVariant('variant-en', ['title' => 'Origin Title', 'price' => '9.99', 'sku' => 'ORIGIN-1']);
        $enVariant->makeLocalization('de')->data([])->save();

        Facades\Site::setSelected('de');

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.variants.index', 'test-product'));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.title', 'Origin Title');
        $response->assertJsonPath('0.price', '9.99');
        $response->assertJsonPath('0.sku', 'ORIGIN-1');
    }

    #[Test]
    public function localized_variant_values_take_precedence_over_origin()
    {
        $this->setupMultisite();

        $enVariant = $this->makeVariant('variant-en', ['title' => 'Origin Title', 'price' => '9.99', 'sku' => 'ORIGIN-1']);
        $enVariant->makeLocalization('de')->data(['title' => 'DE Title', 'price' => '12.00', 'sku' => 'DE-1'])->save();

        Facades\Site::setSelected('de');

        $response = $this->actingAsSuperUser()
            ->getJson(cp_route('shopify.variants.index', 'test-product'));

        $response->assertOk();
        $response->assertJsonPath('0.title', 'DE Title');
        $response->assertJsonPath('0.price', '12.00');
        $response->assertJsonPath('0.sku', 'DE-1');
    }
}
