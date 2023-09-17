<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;

class TagsTest extends TestCase
{
    private function tag($tag)
    {
        return Facades\Parse::template($tag, []);
    }

    /** @test */
    public function outputs_shopify_tokens()
    {
        config()->set('shopify.url', 'abcd');
        config()->set('shopify.storefront_token', '1234');

        $this->assertEquals("
    <script>\r\n
    window.shopifyUrl = 'abcd';\r\n
    window.shopifyToken = '1234';\r\n
    </script>",
            $this->tag('{{ shopify:tokens }}')
        );
    }

    /** @test */
    public function outputs_shopify_scripts()
    {
        $this->assertStringStartsWith("<script", $this->tag('{{ shopify:scripts }}'));
    }

    /** @test */
    public function outputs_product_price()
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
        ])
            ->collection('variants');

        $variant->save();

        $this->assertEqual('£9.99', $this->tag('{{ shopify:product_price }}', ['slug' => 'obi-wan']));
        $this->assertEqual('From £9.99', $this->tag('{{ shopify:product_price show_from="true" }}', ['slug' => 'obi-wan']));

        $variant->merge([
            'inventory_quantity' => 0
        ])->save();

        $this->assertEqual('Out of Stock', $this->tag('{{ shopify:product_price }}', ['slug' => 'obi-wan']));
    }

    /** @test */
    public function outputs_in_stock()
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
        ])
            ->collection('variants');

        $variant->save();

        $this->assertEqual('Yes', $this->tag('{{ if {shopify:in_stock} }}Yes{{ /if }}', ['slug' => 'obi-wan']));

        $variant->merge([
            'inventory_quantity' => 0
        ])->save();

        $this->assertEqual('', $this->tag('{{ if {shopify:in_stock} }}Yes{{ /if }}', ['slug' => 'obi-wan']));
    }

    /** @test */
    public function outputs_product_variants_generate()
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
            'storefront_id' => 'abc'
        ])
            ->collection('variants');

        $variant->save();

        $this->assertEqual('<input type="hidden" name="ss-product-variant" id="ss-product-variant" value="abc">', $this->tag('{{ shopify:variants:generate show_price="true" show_out_of_stock="true" }}', ['slug' => 'obi-wan']));

        $variant->merge([
            'inventory_quantity' => 0
        ])->save();

        $this->assertEqual('', $this->tag('{{ shopify:variants:generate show_price="true" show_out_of_stock="false" }}', ['slug' => 'obi-wan']));

        $variant->merge([
            'inventory_quantity' => 10
        ])->save();

        $variant2 = Facades\Entry::make()->data([
            'title' => 'Another T-shirt',
            'slug' => 'obi-wan-tshirt-2',
            'sku' => 'obi-wan-tshirt-2',
            'product_slug' => 'obi-wan',
            'price' => 10.99,
            'inventory_quantity' => 5,
            'storefront_id' => 'def'
        ])
            ->collection('variants');

        $variant2->save();

        $this->assertEqual('<select name="ss-product-variant" id="ss-product-variant" class="ss-variant-select "><option value="abc" data-in-stock="true">T-shirt - £9.99</option><option value="def" data-in-stock="true">Another T-shirt - £10.99</option></select>', $this->tag('{{ shopify:variants:generate show_price="true" show_out_of_stock="false" }}', ['slug' => 'obi-wan']));
    }

    /** @test */
    public function outputs_product_variants()
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
            'storefront_id' => 'abc'
        ])
            ->collection('variants');

        $variant->save();

        $variant2 = Facades\Entry::make()->data([
            'title' => 'Another T-shirt',
            'slug' => 'obi-wan-tshirt-2',
            'sku' => 'obi-wan-tshirt-2',
            'product_slug' => 'obi-wan',
            'price' => 10.99,
            'inventory_quantity' => 5,
            'storefront_id' => 'def'
        ])
            ->collection('variants');

        $variant2->save();

        $this->assertEqual('abcdef', $this->tag('{{ shopify:variants }}{{ sku }}{{ /shopify:variants }}', ['slug' => 'obi-wan']));

        $this->assertEqual('abc', $this->tag('{{ shopify:variants sku:is="abc" }}{{ sku }}{{ /shopify:variants }}', ['slug' => 'obi-wan']));

    }
}
