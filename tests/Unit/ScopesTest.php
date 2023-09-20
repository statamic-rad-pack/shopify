<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;

class ScopesTest extends TestCase
{
    private function tag($tag, $variables = [])
    {
        return (string) Facades\Parse::template($tag, $variables);
    }

    /** @test */
    public function limits_variants_by_product()
    {
        $product = Facades\Entry::make()->data([
            'title' => 'Obi wan',
            'vendor' => 'Kenobe',
            'slug' => 'obi-wan',
            'product_id' => 1,
        ])
            ->collection('products');

        $product->save();

        $variant = Facades\Entry::make()->data([
            'title' => 'T-shirt',
            'slug' => 'obi-wan-tshirt',
            'sku' => 'obi-wan-tshirt',
            'product_slug' => 'obi-wan',
            'price' => 9.99,
            'inventory_quantity' => 10,
            'inventory_policy' => 'deny',
            'inventory_management' => 'shopify',
        ])
            ->collection('variants');

        $variant->save();

        $variant2 = Facades\Entry::make()->data([
            'title' => 'Another T-shirt',
            'slug' => 'obi-wan-tshirt-2',
            'sku' => 'obi-wan-tshirt-2',
            'product_slug' => 'not-obi-wan',
            'price' => 10.99,
            'inventory_quantity' => 5,
            'storefront_id' => 'def'
        ])
            ->collection('variants');

        $variant2->save();

        $this->assertEquals('obi-wan-tshirt', $this->tag('{{ collection:variants query_scope="variant_by_product" product="obi-wan" }}{{ sku }}{{ /collection:variants }}'));
        $this->assertEquals('obi-wan-tshirt-2', $this->tag('{{ collection:variants query_scope="variant_by_product" product="not-obi-wan" }}{{ sku }}{{ /collection:variants }}'));
    }

    /** @test */
    public function limits_variants_by_is_on_sale()
    {
        $product = Facades\Entry::make()->data([
            'title' => 'Obi wan',
            'vendor' => 'Kenobe',
            'slug' => 'obi-wan',
            'product_id' => 1,
        ])
            ->collection('products');

        $product->save();

        $variant = Facades\Entry::make()->data([
            'title' => 'T-shirt',
            'slug' => 'obi-wan-tshirt',
            'sku' => 'obi-wan-tshirt',
            'product_slug' => 'obi-wan',
            'compare_at_price' => 9.99,
            'price' => 9.99,
            'inventory_quantity' => 10,
            'inventory_policy' => 'deny',
            'inventory_management' => 'shopify',
        ])
            ->collection('variants');

        $variant->save();

        $variant2 = Facades\Entry::make()->data([
            'title' => 'Another T-shirt',
            'slug' => 'obi-wan-tshirt-2',
            'sku' => 'obi-wan-tshirt-2',
            'product_slug' => 'not-obi-wan',
            'compare_at_price' => 12.99,
            'price' => 10.99,
            'inventory_quantity' => 5,
            'storefront_id' => 'def'
        ])
            ->collection('variants');

        $variant2->save();

        $this->assertEquals('obi-wan-tshirt-2,obi-wan-tshirt,', $this->tag('{{ collection:variants }}{{ sku }},{{ /collection:variants }}'));
        $this->assertEquals('obi-wan-tshirt-2,', $this->tag('{{ collection:variants query_scope="variant_is_on_sale" }}{{ sku }},{{ /collection:variants }}'));
    }
}
