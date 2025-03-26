<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Rest;
use Shopify\Clients\RestResponse;
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
            ->collection(config('shopify.collection_handle', 'products'));

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
        $this->mock(Rest::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('post')
                ->andReturn(new RestResponse(
                    status: 201,
                    body: '{"customer_address":{"id": 706405506930370000,"email": "bob@biller.com","accepts_marketing": true,"created_at": "2021-12-31T19:00:00-05:00","updated_at": "2021-12-31T19:00:00-05:00","first_name": "Bob","last_name": "Biller","orders_count": 0,"state": "disabled","total_spent": "0.00","last_order_id": null,"note": "This customer loves ice cream","verified_email": true,"multipass_identifier": null,"tax_exempt": false,"tags": "","last_order_name": null,"currency": "USD","phone": null,"addresses": [],"accepts_marketing_updated_at": "2021-12-31T19:00:00-05:00","marketing_opt_in_level": null,"tax_exemptions": [],"email_marketing_consent": null,"sms_marketing_consent": null,"admin_graphql_api_id": "gid://shopify/Customer/706405506930370084"}}'
                ));
        });

        $response = $this->postJson('/!/shopify/address', []);
        $response->assertStatus(422);

        $response = $this->postJson('/!/shopify/address', ['customer_id' => 1]);
        $response->assertStatus(422);

        $response = $this->postJson('/!/shopify/address', ['customer_id' => 1, 'first_name' => 'a', 'last_name' => 'b', 'address1' => 'c', 'city' => 'd', 'province' => 'e', 'zip' => 'f', 'country' => 'g']);
        $response->assertStatus(200);
    }

    #[Test]
    public function updates_an_address()
    {
        $this->mock(Rest::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('put')
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{"customer_address":{"id": 706405506930370000,"email": "bob@biller.com","accepts_marketing": true,"created_at": "2021-12-31T19:00:00-05:00","updated_at": "2021-12-31T19:00:00-05:00","first_name": "Bob","last_name": "Biller","orders_count": 0,"state": "disabled","total_spent": "0.00","last_order_id": null,"note": "This customer loves ice cream","verified_email": true,"multipass_identifier": null,"tax_exempt": false,"tags": "","last_order_name": null,"currency": "USD","phone": null,"addresses": [],"accepts_marketing_updated_at": "2021-12-31T19:00:00-05:00","marketing_opt_in_level": null,"tax_exemptions": [],"email_marketing_consent": null,"sms_marketing_consent": null,"admin_graphql_api_id": "gid://shopify/Customer/706405506930370084"}}'
                ));
        });

        $response = $this->postJson('/!/shopify/address/1', []);
        $response->assertStatus(422);

        $response = $this->postJson('/!/shopify/address/1', ['customer_id' => 1]);
        $response->assertStatus(422);

        $response = $this->postJson('/!/shopify/address/1', ['customer_id' => 1, 'first_name' => 'a', 'last_name' => 'b', 'address1' => 'c', 'city' => 'd', 'province' => 'e', 'zip' => 'f', 'country' => 'g']);
        $response->assertStatus(200);
    }

    #[Test]
    public function deletes_an_address()
    {
        $this->mock(Rest::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('delete')
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{"customer_address":{"id": 706405506930370000,"email": "bob@biller.com","accepts_marketing": true,"created_at": "2021-12-31T19:00:00-05:00","updated_at": "2021-12-31T19:00:00-05:00","first_name": "Bob","last_name": "Biller","orders_count": 0,"state": "disabled","total_spent": "0.00","last_order_id": null,"note": "This customer loves ice cream","verified_email": true,"multipass_identifier": null,"tax_exempt": false,"tags": "","last_order_name": null,"currency": "USD","phone": null,"addresses": [],"accepts_marketing_updated_at": "2021-12-31T19:00:00-05:00","marketing_opt_in_level": null,"tax_exemptions": [],"email_marketing_consent": null,"sms_marketing_consent": null,"admin_graphql_api_id": "gid://shopify/Customer/706405506930370084"}}'
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
