<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;

class WebhooksTest extends TestCase
{
    #[Test]
    public function creates_a_product()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);

        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();
        Facades\Taxonomy::make()->handle('type')->save();

        $payload = '{"admin_graphql_api_id":"gid:\/\/shopify\/Product\/788032119674292922","body_html":"An example T-Shirt","created_at":null,"handle":"example-t-shirt","id":788032119674292922,"product_type":"Shirts","published_at":"2023-09-18T07:51:36+01:00","template_suffix":null,"title":"Example T-Shirt","updated_at":"2023-09-18T07:51:36+01:00","vendor":"Acme","status":"active","published_scope":"web","tags":"example, mens, t-shirt","variants":[{"admin_graphql_api_id":"gid:\/\/shopify\/ProductVariant\/642667041472713922","barcode":null,"compare_at_price":"24.99","created_at":null,"fulfillment_service":"manual","id":642667041472713922,"inventory_management":"shopify","inventory_policy":"deny","position":0,"price":"19.99","product_id":788032119674292922,"sku":"example-shirt-s","taxable":true,"title":"","updated_at":null,"option1":"Small","option2":null,"option3":null,"grams":200,"image_id":null,"weight":200.0,"weight_unit":"g","inventory_item_id":null,"inventory_quantity":75,"old_inventory_quantity":75,"requires_shipping":true},{"admin_graphql_api_id":"gid:\/\/shopify\/ProductVariant\/757650484644203962","barcode":null,"compare_at_price":"24.99","created_at":null,"fulfillment_service":"manual","id":757650484644203962,"inventory_management":"shopify","inventory_policy":"deny","position":0,"price":"19.99","product_id":788032119674292922,"sku":"example-shirt-m","taxable":true,"title":"","updated_at":null,"option1":"Medium","option2":null,"option3":null,"grams":200,"image_id":null,"weight":200.0,"weight_unit":"g","inventory_item_id":null,"inventory_quantity":50,"old_inventory_quantity":50,"requires_shipping":true}],"options":[],"images":[],"image":null}';

        $response = $this->postJson('/!/shopify/webhook/product/create', json_decode($payload, true));

        $this->assertSame('{"message":"Product has been dispatched to the queue for update"}', $response->getContent());
    }

    #[Test]
    public function updates_a_product()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);

        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();
        Facades\Taxonomy::make()->handle('type')->save();

        $payload = '{"admin_graphql_api_id":"gid:\/\/shopify\/Product\/788032119674292922","body_html":"An example T-Shirt","created_at":null,"handle":"example-t-shirt","id":788032119674292922,"product_type":"Shirts","published_at":"2023-09-18T07:51:36+01:00","template_suffix":null,"title":"Example T-Shirt","updated_at":"2023-09-18T07:51:36+01:00","vendor":"Acme","status":"active","published_scope":"web","tags":"example, mens, t-shirt","variants":[{"admin_graphql_api_id":"gid:\/\/shopify\/ProductVariant\/642667041472713922","barcode":null,"compare_at_price":"24.99","created_at":null,"fulfillment_service":"manual","id":642667041472713922,"inventory_management":"shopify","inventory_policy":"deny","position":0,"price":"19.99","product_id":788032119674292922,"sku":"example-shirt-s","taxable":true,"title":"","updated_at":null,"option1":"Small","option2":null,"option3":null,"grams":200,"image_id":null,"weight":200.0,"weight_unit":"g","inventory_item_id":null,"inventory_quantity":75,"old_inventory_quantity":75,"requires_shipping":true},{"admin_graphql_api_id":"gid:\/\/shopify\/ProductVariant\/757650484644203962","barcode":null,"compare_at_price":"24.99","created_at":null,"fulfillment_service":"manual","id":757650484644203962,"inventory_management":"shopify","inventory_policy":"deny","position":0,"price":"19.99","product_id":788032119674292922,"sku":"example-shirt-m","taxable":true,"title":"","updated_at":null,"option1":"Medium","option2":null,"option3":null,"grams":200,"image_id":null,"weight":200.0,"weight_unit":"g","inventory_item_id":null,"inventory_quantity":50,"old_inventory_quantity":50,"requires_shipping":true}],"options":[],"images":[],"image":null}';

        $response = $this->postJson('/!/shopify/webhook/product/update', json_decode($payload, true));

        $this->assertSame('{"message":"Product has been dispatched to the queue for update"}', $response->getContent());
    }

    #[Test]
    public function deletes_a_product()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);

        $payload = '{"id":788032119674292922}';

        $response = $this->postJson('/!/shopify/webhook/product/delete', json_decode($payload, true));

        $this->assertSame('{"message":"Product has been deleted"}', $response->getContent());
    }

    #[Test]
    public function creates_a_customer()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);

        $payload = '{"id": 706405506930370000,"email": "bob@biller.com","accepts_marketing": true,"created_at": "2021-12-31T19:00:00-05:00","updated_at": "2021-12-31T19:00:00-05:00","first_name": "Bob","last_name": "Biller","orders_count": 0,"state": "disabled","total_spent": "0.00","last_order_id": null,"note": "This customer loves ice cream","verified_email": true,"multipass_identifier": null,"tax_exempt": false,"tags": "","last_order_name": null,"currency": "USD","phone": null,"addresses": [],"accepts_marketing_updated_at": "2021-12-31T19:00:00-05:00","marketing_opt_in_level": null,"tax_exemptions": [],"email_marketing_consent": null,"sms_marketing_consent": null,"admin_graphql_api_id": "gid://shopify/Customer/706405506930370084"}';

        $response = $this->postJson('/!/shopify/webhook/customer/create', json_decode($payload, true));

        $this->assertSame('{"message":"Customer has been updated"}', $response->getContent());
    }

    #[Test]
    public function updates_a_customer()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);

        Facades\Taxonomy::make()->handle('tags')->save();
        Facades\Taxonomy::make()->handle('vendor')->save();
        Facades\Taxonomy::make()->handle('type')->save();

        $payload = '{"id": 706405506930370000,"email": "bob@biller.com","accepts_marketing": true,"created_at": "2021-12-31T19:00:00-05:00","updated_at": "2021-12-31T19:00:00-05:00","first_name": "Bob","last_name": "Biller","orders_count": 0,"state": "disabled","total_spent": "0.00","last_order_id": null,"note": "This customer loves ice cream","verified_email": true,"multipass_identifier": null,"tax_exempt": false,"tags": "","last_order_name": null,"currency": "USD","phone": null,"addresses": [],"accepts_marketing_updated_at": "2021-12-31T19:00:00-05:00","marketing_opt_in_level": null,"tax_exemptions": [],"email_marketing_consent": null,"sms_marketing_consent": null,"admin_graphql_api_id": "gid://shopify/Customer/706405506930370084"}';

        $response = $this->postJson('/!/shopify/webhook/customer/update', json_decode($payload, true));

        $this->assertSame('{"message":"Customer has been updated"}', $response->getContent());
    }

    #[Test]
    public function deletes_a_customer()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);

        $payload = '{"id":788032119674292922}';

        $response = $this->postJson('/!/shopify/webhook/customer/delete', json_decode($payload, true));

        $this->assertSame('{"message":"Customer has been deleted"}', $response->getContent());
    }
}
