<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;

class MultiStoreMarketsTagsTest extends TestCase
{
    private function tag($tag, $variables = [])
    {
        return (string) Facades\Parse::template($tag, $variables);
    }

    private function marketsConfig(): array
    {
        return [
            'enabled' => true,
            'mode' => 'markets',
            'markets' => [
                'GB' => ['currency' => '£'],
                'IE' => ['currency' => '€'],
            ],
        ];
    }

    private function makeProductWithVariant(array $variantData = []): void
    {
        $product = Facades\Entry::make()->data([
            'title' => 'Test Product',
            'slug' => 'test-product',
            'product_id' => '1',
        ])->collection(config('shopify.collection_handle', 'products'));

        $product->save();

        $variant = Facades\Entry::make()->data(array_merge([
            'title' => 'Default',
            'slug' => 'test-variant',
            'sku' => 'test-sku',
            'product_slug' => 'test-product',
            'price' => '10.00',
            'inventory_quantity' => 5,
            'inventory_policy' => 'deny',
        ], $variantData))->collection('variants');

        $variant->save();
    }

    #[Test]
    public function tokens_returns_currency_for_market()
    {
        config()->set('shopify.multi_store', $this->marketsConfig());
        config()->set('shopify.url', 'single-store.myshopify.com');
        config()->set('shopify.storefront_token', 'single-token');

        $output = str_replace(["\r", "\n"], '', $this->tag('{{ shopify:tokens store="GB" }}'));

        $this->assertStringContainsString("currency: '£'", $output);
        $this->assertStringContainsString("url: 'single-store.myshopify.com'", $output);
        $this->assertStringContainsString("token: 'single-token'", $output);
    }

    #[Test]
    public function product_price_reads_from_market_data()
    {
        config()->set('shopify.multi_store', $this->marketsConfig());

        $this->makeProductWithVariant([
            'price' => '10.00',
            'inventory_quantity' => 5,
            'inventory_policy' => 'deny',
            'market_data' => [
                'GB' => ['price' => '9.99', 'compare_at_price' => null, 'inventory_quantity' => 5],
                'IE' => ['price' => '12.99', 'compare_at_price' => null, 'inventory_quantity' => 3],
            ],
        ]);

        $gbPrice = $this->tag('{{ shopify:product_price store="GB" }}', ['slug' => 'test-product']);
        $iePrice = $this->tag('{{ shopify:product_price store="IE" }}', ['slug' => 'test-product']);
        $defaultPrice = $this->tag('{{ shopify:product_price }}', ['slug' => 'test-product']);

        $this->assertStringContainsString('9.99', $gbPrice);
        $this->assertStringContainsString('12.99', $iePrice);
        $this->assertStringContainsString('10.00', $defaultPrice);
    }

    #[Test]
    public function in_stock_reads_from_market_data()
    {
        config()->set('shopify.multi_store', $this->marketsConfig());

        $this->makeProductWithVariant([
            'price' => '10.00',
            'inventory_quantity' => 5,
            'inventory_policy' => 'deny',
            'market_data' => [
                'GB' => ['price' => '9.99', 'compare_at_price' => null, 'inventory_quantity' => 0],
                'IE' => ['price' => '12.99', 'compare_at_price' => null, 'inventory_quantity' => 10],
            ],
        ]);

        // GB has 0 inventory — should be out of stock
        $gbInStock = $this->tag('{{ shopify:in_stock store="GB" }}', ['slug' => 'test-product']);
        // IE has 10 inventory — should be in stock
        $ieInStock = $this->tag('{{ shopify:in_stock store="IE" }}', ['slug' => 'test-product']);

        $this->assertEquals('', $gbInStock);
        $this->assertEquals('1', $ieInStock);
    }

    #[Test]
    public function variants_tag_overrides_pricing_from_market_data()
    {
        config()->set('shopify.multi_store', $this->marketsConfig());

        $this->makeProductWithVariant([
            'price' => '10.00',
            'inventory_quantity' => 5,
            'inventory_policy' => 'deny',
            'market_data' => [
                'GB' => ['price' => '9.99', 'compare_at_price' => null, 'inventory_quantity' => 5],
            ],
        ]);

        $gbVariants = $this->tag('{{ shopify:variants store="GB" }}{{ price }}{{ /shopify:variants }}', ['slug' => 'test-product']);
        $defaultVariants = $this->tag('{{ shopify:variants }}{{ price }}{{ /shopify:variants }}', ['slug' => 'test-product']);

        $this->assertSame('9.99', $gbVariants);
        $this->assertSame('10.00', $defaultVariants);
    }
}
