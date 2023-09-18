<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;

class TagsTest extends TestCase
{
    private function tag($tag, $variables = [])
    {
        return (string) Facades\Parse::template($tag, $variables);
    }

    /** @test */
    public function outputs_shopify_tokens()
    {
        config()->set('shopify.url', 'abcd');
        config()->set('shopify.storefront_token', '1234');

        $this->assertEquals(str_replace(["\r", "\n"], '', "<script>
window.shopifyUrl = 'abcd';
window.shopifyToken = '1234';
</script>"),
            str_replace(["\r", "\n"], '', $this->tag('{{ shopify:tokens }}'))
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
            'inventory_policy' => 'deny',
            'inventory_management' => 'shopify',
        ])
            ->collection('variants');



        $variant->save();

        $this->assertEquals('£9.99', $this->tag('{{ shopify:product_price }}', ['slug' => 'obi-wan']));

        $variant->merge([
            'inventory_quantity' => 0
        ])->save();

        $this->assertEquals('Out of Stock', $this->tag('{{ shopify:product_price }}', ['slug' => 'obi-wan']));

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

        $this->assertEquals('From £9.99', $this->tag('{{ shopify:product_price show_from="true" }}', ['slug' => 'obi-wan']));

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
            'inventory_policy' => 'deny',
            'inventory_management' => 'shopify',
        ])
            ->collection('variants');

        $variant->save();

        $this->assertEquals('Yes', $this->tag('{{ if {shopify:in_stock} }}Yes{{ /if }}', ['slug' => 'obi-wan']));

        $variant->merge([
            'inventory_quantity' => 0
        ])->save();

        $this->assertEquals('', $this->tag('{{ if {shopify:in_stock} }}Yes{{ /if }}', ['slug' => 'obi-wan']));
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
            'storefront_id' => 'abc',
            'inventory_policy' => 'deny',
            'inventory_management' => 'shopify',
        ])
            ->collection('variants');

        $variant->save();

        $tagOutput = (string) $this->tag('{{ shopify:variants:generate show_price="true" show_out_of_stock="true" }}', ['slug' => 'obi-wan']);

        $this->assertEquals('<input type="hidden" name="ss-product-variant" id="ss-product-variant" value="abc">', trim($tagOutput));

        $variant2 = Facades\Entry::make()->data([
            'title' => 'Another T-shirt',
            'slug' => 'obi-wan-tshirt-2',
            'sku' => 'obi-wan-tshirt-2',
            'product_slug' => 'obi-wan',
            'price' => 10.99,
            'inventory_quantity' => 5,
            'storefront_id' => 'def',
            'inventory_policy' => 'deny',
            'inventory_management' => 'shopify',
        ])
            ->collection('variants');

        $variant2->save();

        $tagOutput = $this->tag('{{ shopify:variants:generate show_price="true" show_out_of_stock="false" }}', ['slug' => 'obi-wan']);
        $tagOutput = str_replace(["\r", "\n", "\t"], '', $tagOutput);
        $tagOutput = preg_replace('/\>\s+\</m', '><', trim($tagOutput));

        $this->assertEquals('<select name="ss-product-variant" id="ss-product-variant" class="ss-variant-select "><option value="abc" data-in-stock="true">T-shirt - £9.99</option><option value="def" data-in-stock="true">Another T-shirt - £10.99</option></select>', $tagOutput);

        $variant->merge([
            'inventory_quantity' => 0
        ])->save();

        $tagOutput = $this->tag('{{ shopify:variants:generate show_price="true" show_out_of_stock="false" }}', ['slug' => 'obi-wan']);
        $tagOutput = str_replace(["\r", "\n", "\t"], '', $tagOutput);
        $tagOutput = preg_replace('/\>\s+\</m', '><', trim($tagOutput));

        $this->assertEquals('<select name="ss-product-variant" id="ss-product-variant" class="ss-variant-select "><option value="abc" data-in-stock="false" disabled>T-shirt - £9.99</option><option value="def" data-in-stock="true">Another T-shirt - £10.99</option></select>', $tagOutput);

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
            'storefront_id' => 'abc',
            'inventory_policy' => 'deny',
            'inventory_management' => 'shopify',
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
            'storefront_id' => 'def',
            'inventory_policy' => 'deny',
            'inventory_management' => 'shopify',
        ])
            ->collection('variants');

        $variant2->save();

        $this->assertEquals('abcdef', $this->tag('{{ shopify:variants }}{{ storefront_id }}{{ /shopify:variants }}', ['slug' => 'obi-wan']));

        $this->assertEquals('abc', $this->tag('{{ shopify:variants storefront_id:is="abc" }}{{ storefront_id }}{{ /shopify:variants }}', ['slug' => 'obi-wan']));
    }
}
