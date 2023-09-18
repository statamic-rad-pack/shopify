<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;

class ActionsTest extends TestCase
{
    /** @test */
    public function gets_correct_data_from_action_url()
    {
        $product = Facades\Entry::make()
            ->data([
                'title' => 'Obi wan',
                'vendor' => 'Kenobe',
                'slug' => 'obi-wan',
                'product_id' => 1,
            ])
            ->collection('products');

        $product->save();

        $variant = Facades\Entry::make()
            ->data([
                'title' => 'T-shirt',
                'slug' => 'obi-wan-tshirt',
                'sku' => 'obi-wan-tshirt',
                'product_slug' => 'obi-wan',
                'price' => 9.99,
                'inventory_quantity' => 10,
                'storefront_id' => 'abc',
                'option1' => 'a',
            ])
            ->collection('variants');

        $variant->save();

        $variant2 = Facades\Entry::make()
            ->data([
                'title' => 'Another T-shirt',
                'slug' => 'obi-wan-tshirt-2',
                'sku' => 'obi-wan-tshirt-2',
                'product_slug' => 'obi-wan',
                'price' => 10.99,
                'inventory_quantity' => 5,
                'storefront_id' => 'def',
                'option1' => 'b',
            ])
            ->collection('variants');

        $variant2->save();

        $response = $this->get('/!/shopify/variants/obi-wan');
        $this->assertSame('[{"title":"T-shirt","storefront_id":"abc","price":9.99,"inventory_quantity":10},{"title":"Another T-shirt","storefront_id":"def","price":10.99,"inventory_quantity":5}]', $response->getContent());

        $response = $this->get('/!/shopify/variants/obi-wan?option1=a');
        $this->assertSame('[{"title":"T-shirt","storefront_id":"abc","price":9.99,"inventory_quantity":10}]', $response->getContent());
    }
}
