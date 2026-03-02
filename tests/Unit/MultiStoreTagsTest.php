<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;

class MultiStoreTagsTest extends TestCase
{
    private function tag($tag, $variables = [])
    {
        return (string) Facades\Parse::template($tag, $variables);
    }

    private function multiStoreConfig(): array
    {
        return [
            'enabled' => true,
            'mode' => 'unified',
            'primary_store' => 'uk',
            'stores' => [
                'uk' => [
                    'url' => 'uk-store.myshopify.com',
                    'storefront_token' => 'uk-storefront-token',
                    'admin_token' => 'uk-token',
                    'api_version' => '2025-04',
                    'currency' => '£',
                ],
                'us' => [
                    'url' => 'us-store.myshopify.com',
                    'storefront_token' => 'us-storefront-token',
                    'admin_token' => 'us-token',
                    'api_version' => '2025-04',
                    'currency' => '$',
                ],
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
    public function outputs_store_specific_tokens()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());

        $output = str_replace(["\r", "\n"], '', $this->tag('{{ shopify:tokens store="uk" }}'));

        $this->assertStringContainsString("url: 'uk-store.myshopify.com'", $output);
        $this->assertStringContainsString("token: 'uk-storefront-token'", $output);
        $this->assertStringContainsString("currency: '£'", $output);
    }

    #[Test]
    public function outputs_different_store_tokens()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());

        $output = str_replace(["\r", "\n"], '', $this->tag('{{ shopify:tokens store="us" }}'));

        $this->assertStringContainsString("url: 'us-store.myshopify.com'", $output);
        $this->assertStringContainsString("token: 'us-storefront-token'", $output);
        $this->assertStringContainsString("currency: '$'", $output);
    }

    #[Test]
    public function single_store_tokens_remain_unchanged()
    {
        config()->set('shopify.url', 'single-store.myshopify.com');
        config()->set('shopify.storefront_token', 'single-token');

        $output = str_replace(["\r", "\n"], '', $this->tag('{{ shopify:tokens }}'));

        $this->assertEquals(
            str_replace(["\r", "\n"], '', "<script>window.shopifyConfig = { url: 'single-store.myshopify.com', token: 'single-token', apiVersion: '2025-04' };</script>"),
            $output
        );
    }

    #[Test]
    public function product_price_reads_from_store_specific_multi_store_data()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());

        $this->makeProductWithVariant([
            'price' => '10.00',
            'inventory_quantity' => 5,
            'inventory_policy' => 'deny',
            'multi_store_data' => [
                'uk' => ['price' => '19.99', 'inventory_quantity' => 5, 'inventory_policy' => 'deny'],
                'us' => ['price' => '24.99', 'inventory_quantity' => 3, 'inventory_policy' => 'deny'],
            ],
        ]);

        $ukPrice = $this->tag('{{ shopify:product_price store="uk" }}', ['slug' => 'test-product']);
        $usPrice = $this->tag('{{ shopify:product_price store="us" }}', ['slug' => 'test-product']);
        $defaultPrice = $this->tag('{{ shopify:product_price }}', ['slug' => 'test-product']);

        $this->assertStringContainsString('19.99', $ukPrice);
        $this->assertStringContainsString('24.99', $usPrice);
        $this->assertStringContainsString('10.00', $defaultPrice);
    }

    #[Test]
    public function in_stock_reads_from_store_specific_multi_store_data()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());

        $this->makeProductWithVariant([
            'price' => '10.00',
            'inventory_quantity' => 5,
            'inventory_policy' => 'deny',
            'multi_store_data' => [
                'uk' => ['price' => '19.99', 'inventory_quantity' => 0, 'inventory_policy' => 'deny'],
                'us' => ['price' => '24.99', 'inventory_quantity' => 10, 'inventory_policy' => 'deny'],
            ],
        ]);

        // UK store has 0 inventory — should be out of stock
        $ukInStock = $this->tag('{{ shopify:in_stock store="uk" }}', ['slug' => 'test-product']);
        // US store has 10 inventory — should be in stock
        $usInStock = $this->tag('{{ shopify:in_stock store="us" }}', ['slug' => 'test-product']);

        $this->assertEquals('', $ukInStock); // out of stock returns false/empty
        $this->assertEquals('1', $usInStock); // in stock returns true/1
    }

    #[Test]
    public function variants_tag_overrides_pricing_from_store_data()
    {
        config()->set('shopify.multi_store', $this->multiStoreConfig());

        $this->makeProductWithVariant([
            'price' => '10.00',
            'inventory_quantity' => 5,
            'inventory_policy' => 'deny',
            'multi_store_data' => [
                'uk' => ['price' => '19.99', 'inventory_quantity' => 5, 'inventory_policy' => 'deny'],
            ],
        ]);

        $ukVariants = $this->tag('{{ shopify:variants store="uk" }}{{ price }}{{ /shopify:variants }}', ['slug' => 'test-product']);
        $defaultVariants = $this->tag('{{ shopify:variants }}{{ price }}{{ /shopify:variants }}', ['slug' => 'test-product']);

        $this->assertSame('19.99', $ukVariants);
        $this->assertSame('10.00', $defaultVariants);
    }
}
