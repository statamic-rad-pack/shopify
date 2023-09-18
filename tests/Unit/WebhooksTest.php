<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;

class WebhooksTest extends TestCase
{
    /** @test */
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

    /** @test */
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

    /** @test */
    public function deletes_a_product()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);

        $payload = '{"id":788032119674292922}';

        $response = $this->postJson('/!/shopify/webhook/product/delete', json_decode($payload, true));

        $this->assertSame('{"message":"Product has been deleted"}', $response->getContent());
    }
}
