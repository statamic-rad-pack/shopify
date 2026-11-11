<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Support\ProductSummary;
use StatamicRadPack\Shopify\Tests\TestCase;

class ProductSummaryTest extends TestCase
{
    #[Test]
    public function summarises_variant_prices_and_stock()
    {
        $summary = ProductSummary::fromVariants([
            ['price' => '19.99', 'compare_at_price' => null, 'inventory_policy' => 'DENY', 'inventory_quantity' => 0],
            ['price' => '9.50', 'compare_at_price' => '12.00', 'inventory_policy' => 'DENY', 'inventory_quantity' => 3],
            ['price' => '100.00', 'compare_at_price' => null, 'inventory_policy' => 'DENY', 'inventory_quantity' => 0],
        ]);

        $this->assertSame([
            'price_min' => '9.50',
            'price_max' => '100.00',
            'in_stock' => true,
            'on_sale' => true,
        ], $summary);
    }

    #[Test]
    public function summarises_a_product_without_variants()
    {
        $this->assertSame([
            'price_min' => null,
            'price_max' => null,
            'in_stock' => false,
            'on_sale' => false,
        ], ProductSummary::fromVariants([]));
    }

    #[Test]
    public function syncs_the_product_when_a_variant_changes()
    {
        $product = tap(Facades\Entry::make()->collection(config('shopify.collection_handle', 'products'))->slug('obi-wan')->data(['product_id' => 1]))->save();

        $variant = tap(Facades\Entry::make()->collection('variants')->slug('1')->data([
            'product_slug' => 'obi-wan',
            'price' => '10.00',
            'inventory_policy' => 'DENY',
            'inventory_quantity' => 0,
        ]))->save();

        ProductSummary::syncForVariant($variant);
        $this->assertFalse($product->fresh()->get('in_stock'));

        $variant->set('inventory_quantity', 5)->save();
        ProductSummary::syncForVariant($variant);

        $this->assertTrue($product->fresh()->get('in_stock'));
        $this->assertSame('10.00', $product->fresh()->get('price_min'));
    }
}
