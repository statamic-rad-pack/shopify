<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;

class ActionsTest extends TestCase
{
    #[Test]
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

    #[Test]
    public function creates_an_address()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "customerAddressCreate": {
                          "address": {
                            "id": "gid://shopify/MailingAddress/1?model_name=CustomerAddress",
                            "firstName": "First",
                            "lastName": "Name",
                            "company": "Company",
                            "address1": "Line 1",
                            "address2": "Line 2",
                            "city": "City",
                            "province": "Province",
                            "country": "Country",
                            "zip": "Zip",
                            "phone": "Phone",
                            "name": "Name"
                          },
                          "userErrors": []
                        }
                      }
                  }'
                ));
        });

        $response = $this->postJson('/!/shopify/address', []);
        $response->assertStatus(422);

        $response = $this->postJson('/!/shopify/address', ['customer_id' => 1]);
        $response->assertStatus(422);

        $response = $this->postJson('/!/shopify/address', ['customer_id' => 1, 'firstName' => 'a', 'lastName' => 'b', 'address1' => 'c', 'city' => 'd', 'province' => 'e', 'zip' => 'f', 'country' => 'g']);
        $response->assertStatus(200);
    }

    #[Test]
    public function updates_an_address()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "customerAddressUpdate": {
                          "address": {
                            "id": "gid://shopify/MailingAddress/1?model_name=CustomerAddress",
                            "firstName": "First",
                            "lastName": "Name",
                            "company": "Company",
                            "address1": "Line 1",
                            "address2": "Line 2",
                            "city": "City",
                            "province": "Province",
                            "country": "Country",
                            "zip": "Zip",
                            "phone": "Phone",
                            "name": "Name"
                          },
                          "userErrors": []
                        }
                      }
                  }'
                ));
        });

        $response = $this->postJson('/!/shopify/address/1', []);
        $response->assertStatus(422);

        $response = $this->postJson('/!/shopify/address/1', ['customer_id' => 1]);
        $response->assertStatus(422);

        $response = $this->postJson('/!/shopify/address/1', ['customer_id' => 1, 'firstName' => 'a', 'lastName' => 'b', 'address1' => 'c', 'city' => 'd', 'province' => 'e', 'zip' => 'f', 'country' => 'g']);
        $response->assertStatus(200);
    }

    #[Test]
    public function deletes_an_address()
    {
        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{
                      "data": {
                        "customerAddressDelete": {
                          "deletedAddressId":  "gid://shopify/MailingAddress/1?model_name=CustomerAddress",
                          "userErrors": []
                        }
                      }
                  }'
                ));
        });

        $response = $this->postJson('/!/shopify/address/1', ['_method' => 'delete']);
        $response->assertStatus(422);

        $response = $this->postJson('/!/shopify/address/1', ['_method' => 'delete', 'customer_id' => 1]);
        $response->assertStatus(200);
    }

    #[Test]
    public function can_use_precognition_when_creating_an_address()
    {
        $response = $this
            ->withPrecognition()
            ->postJson('/!/shopify/address', []);

        $this->assertSame('true', $response->headers->get('precognition'));
        $response->assertStatus(422);
    }

    #[Test]
    public function can_use_precognition_when_updating_an_address()
    {
        $response = $this
            ->withPrecognition()
            ->postJson('/!/shopify/address/1', []);

        $this->assertSame('true', $response->headers->get('precognition'));
        $response->assertStatus(422);
    }
}
