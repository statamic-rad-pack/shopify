<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use Statamic\Facades;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
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

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{}' // we dont care about the response here
                ));
        });

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

        $this->mock(Graphql::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('query')
                ->andReturn(new HttpResponse(
                    status: 200,
                    body: '{}' // we dont care about the response here
                ));
        });

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

    #[Test]
    public function dispatches_product_update_jobs_for_orders()
    {
        config()->set('shopify.ignore_webhook_integrity_check', true);

        $payload = '{
  "id": 820982911946154508,
  "admin_graphql_api_id": "gid://shopify/Order/820982911946154508",
  "app_id": null,
  "browser_ip": null,
  "buyer_accepts_marketing": true,
  "cancel_reason": "customer",
  "cancelled_at": "2021-12-31T19:00:00-05:00",
  "cart_token": null,
  "checkout_id": null,
  "checkout_token": null,
  "client_details": null,
  "closed_at": null,
  "confirmation_number": null,
  "confirmed": false,
  "contact_email": "jon@example.com",
  "created_at": "2021-12-31T19:00:00-05:00",
  "currency": "USD",
  "current_shipping_price_set": {
    "shop_money": {
      "amount": "0.00",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "0.00",
      "currency_code": "USD"
    }
  },
  "current_subtotal_price": "369.97",
  "current_subtotal_price_set": {
    "shop_money": {
      "amount": "369.97",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "369.97",
      "currency_code": "USD"
    }
  },
  "current_total_additional_fees_set": null,
  "current_total_discounts": "0.00",
  "current_total_discounts_set": {
    "shop_money": {
      "amount": "0.00",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "0.00",
      "currency_code": "USD"
    }
  },
  "current_total_duties_set": null,
  "current_total_price": "369.97",
  "current_total_price_set": {
    "shop_money": {
      "amount": "369.97",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "369.97",
      "currency_code": "USD"
    }
  },
  "current_total_tax": "0.00",
  "current_total_tax_set": {
    "shop_money": {
      "amount": "0.00",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "0.00",
      "currency_code": "USD"
    }
  },
  "customer_locale": "en",
  "device_id": null,
  "discount_codes": [],
  "duties_included": false,
  "email": "jon@example.com",
  "estimated_taxes": false,
  "financial_status": "voided",
  "fulfillment_status": null,
  "landing_site": null,
  "landing_site_ref": null,
  "location_id": null,
  "merchant_business_entity_id": "MTU0ODM4MDAwOQ",
  "merchant_of_record_app_id": null,
  "name": "#9999",
  "note": null,
  "note_attributes": [],
  "number": 234,
  "order_number": 1234,
  "order_status_url": "https://jsmith.myshopify.com/548380009/orders/123456abcd/authenticate?key=abcdefg",
  "original_total_additional_fees_set": null,
  "original_total_duties_set": null,
  "payment_gateway_names": [
    "visa",
    "bogus"
  ],
  "phone": null,
  "po_number": null,
  "presentment_currency": "USD",
  "processed_at": "2021-12-31T19:00:00-05:00",
  "reference": null,
  "referring_site": null,
  "source_identifier": null,
  "source_name": "web",
  "source_url": null,
  "subtotal_price": "359.97",
  "subtotal_price_set": {
    "shop_money": {
      "amount": "359.97",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "359.97",
      "currency_code": "USD"
    }
  },
  "tags": "tag1, tag2",
  "tax_exempt": false,
  "tax_lines": [],
  "taxes_included": false,
  "test": true,
  "token": "123456abcd",
  "total_cash_rounding_payment_adjustment_set": {
    "shop_money": {
      "amount": "0.00",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "0.00",
      "currency_code": "USD"
    }
  },
  "total_cash_rounding_refund_adjustment_set": {
    "shop_money": {
      "amount": "0.00",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "0.00",
      "currency_code": "USD"
    }
  },
  "total_discounts": "20.00",
  "total_discounts_set": {
    "shop_money": {
      "amount": "20.00",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "20.00",
      "currency_code": "USD"
    }
  },
  "total_line_items_price": "369.97",
  "total_line_items_price_set": {
    "shop_money": {
      "amount": "369.97",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "369.97",
      "currency_code": "USD"
    }
  },
  "total_outstanding": "369.97",
  "total_price": "359.97",
  "total_price_set": {
    "shop_money": {
      "amount": "359.97",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "359.97",
      "currency_code": "USD"
    }
  },
  "total_shipping_price_set": {
    "shop_money": {
      "amount": "10.00",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "10.00",
      "currency_code": "USD"
    }
  },
  "total_tax": "0.00",
  "total_tax_set": {
    "shop_money": {
      "amount": "0.00",
      "currency_code": "USD"
    },
    "presentment_money": {
      "amount": "0.00",
      "currency_code": "USD"
    }
  },
  "total_tip_received": "0.00",
  "total_weight": 0,
  "updated_at": "2021-12-31T19:00:00-05:00",
  "user_id": null,
  "billing_address": {
    "first_name": "Steve",
    "address1": "123 Shipping Street",
    "phone": "555-555-SHIP",
    "city": "Shippington",
    "zip": "40003",
    "province": "Kentucky",
    "country": "United States",
    "last_name": "Shipper",
    "address2": null,
    "company": "Shipping Company",
    "latitude": null,
    "longitude": null,
    "name": "Steve Shipper",
    "country_code": "US",
    "province_code": "KY"
  },
  "customer": {
    "id": 115310627314723954,
    "email": "john@example.com",
    "created_at": null,
    "updated_at": null,
    "first_name": "John",
    "last_name": "Smith",
    "state": "disabled",
    "note": null,
    "verified_email": true,
    "multipass_identifier": null,
    "tax_exempt": false,
    "phone": null,
    "currency": "USD",
    "tax_exemptions": [],
    "admin_graphql_api_id": "gid://shopify/Customer/115310627314723954",
    "default_address": {
      "id": 715243470612851245,
      "customer_id": 115310627314723954,
      "first_name": null,
      "last_name": null,
      "company": null,
      "address1": "123 Elm St.",
      "address2": null,
      "city": "Ottawa",
      "province": "Ontario",
      "country": "Canada",
      "zip": "K2H7A8",
      "phone": "123-123-1234",
      "name": "",
      "province_code": "ON",
      "country_code": "CA",
      "country_name": "Canada",
      "default": true
    }
  },
  "discount_applications": [],
  "fulfillments": [],
  "line_items": [
    {
      "id": 487817672276298554,
      "admin_graphql_api_id": "gid://shopify/LineItem/487817672276298554",
      "attributed_staffs": [
        {
          "id": "gid://shopify/StaffMember/902541635",
          "quantity": 1
        }
      ],
      "current_quantity": 1,
      "fulfillable_quantity": 1,
      "fulfillment_service": "manual",
      "fulfillment_status": null,
      "gift_card": false,
      "grams": 100,
      "name": "Aviator sunglasses",
      "price": "89.99",
      "price_set": {
        "shop_money": {
          "amount": "89.99",
          "currency_code": "USD"
        },
        "presentment_money": {
          "amount": "89.99",
          "currency_code": "USD"
        }
      },
      "product_exists": true,
      "product_id": 788032119674292922,
      "properties": [],
      "quantity": 1,
      "requires_shipping": true,
      "sales_line_item_group_id": null,
      "sku": "SKU2006-001",
      "taxable": true,
      "title": "Aviator sunglasses",
      "total_discount": "0.00",
      "total_discount_set": {
        "shop_money": {
          "amount": "0.00",
          "currency_code": "USD"
        },
        "presentment_money": {
          "amount": "0.00",
          "currency_code": "USD"
        }
      },
      "variant_id": null,
      "variant_inventory_management": null,
      "variant_title": null,
      "vendor": null,
      "tax_lines": [],
      "duties": [],
      "discount_allocations": []
    },
    {
      "id": 976318377106520349,
      "admin_graphql_api_id": "gid://shopify/LineItem/976318377106520349",
      "attributed_staffs": [],
      "current_quantity": 1,
      "fulfillable_quantity": 1,
      "fulfillment_service": "manual",
      "fulfillment_status": null,
      "gift_card": false,
      "grams": 1000,
      "name": "Mid-century lounger",
      "price": "159.99",
      "price_set": {
        "shop_money": {
          "amount": "159.99",
          "currency_code": "USD"
        },
        "presentment_money": {
          "amount": "159.99",
          "currency_code": "USD"
        }
      },
      "product_exists": true,
      "product_id": 788032119674292923,
      "properties": [],
      "quantity": 1,
      "requires_shipping": true,
      "sales_line_item_group_id": 142831562,
      "sku": "SKU2006-020",
      "taxable": true,
      "title": "Mid-century lounger",
      "total_discount": "0.00",
      "total_discount_set": {
        "shop_money": {
          "amount": "0.00",
          "currency_code": "USD"
        },
        "presentment_money": {
          "amount": "0.00",
          "currency_code": "USD"
        }
      },
      "variant_id": null,
      "variant_inventory_management": null,
      "variant_title": null,
      "vendor": null,
      "tax_lines": [],
      "duties": [],
      "discount_allocations": []
    },
    {
      "id": 315789986012684393,
      "admin_graphql_api_id": "gid://shopify/LineItem/315789986012684393",
      "attributed_staffs": [],
      "current_quantity": 1,
      "fulfillable_quantity": 1,
      "fulfillment_service": "manual",
      "fulfillment_status": null,
      "gift_card": false,
      "grams": 500,
      "name": "Coffee table",
      "price": "119.99",
      "price_set": {
        "shop_money": {
          "amount": "119.99",
          "currency_code": "USD"
        },
        "presentment_money": {
          "amount": "119.99",
          "currency_code": "USD"
        }
      },
      "product_exists": true,
      "product_id": 788032119674292924,
      "properties": [],
      "quantity": 1,
      "requires_shipping": true,
      "sales_line_item_group_id": 142831562,
      "sku": "SKU2006-035",
      "taxable": true,
      "title": "Coffee table",
      "total_discount": "0.00",
      "total_discount_set": {
        "shop_money": {
          "amount": "0.00",
          "currency_code": "USD"
        },
        "presentment_money": {
          "amount": "0.00",
          "currency_code": "USD"
        }
      },
      "variant_id": null,
      "variant_inventory_management": null,
      "variant_title": null,
      "vendor": null,
      "tax_lines": [],
      "duties": [],
      "discount_allocations": []
    }
  ],
  "payment_terms": null,
  "refunds": [],
  "shipping_address": {
    "first_name": "Steve",
    "address1": "123 Shipping Street",
    "phone": "555-555-SHIP",
    "city": "Shippington",
    "zip": "40003",
    "province": "Kentucky",
    "country": "United States",
    "last_name": "Shipper",
    "address2": null,
    "company": "Shipping Company",
    "latitude": null,
    "longitude": null,
    "name": "Steve Shipper",
    "country_code": "US",
    "province_code": "KY"
  },
  "shipping_lines": [
    {
      "id": 271878346596884015,
      "carrier_identifier": null,
      "code": null,
      "current_discounted_price_set": {
        "shop_money": {
          "amount": "0.00",
          "currency_code": "USD"
        },
        "presentment_money": {
          "amount": "0.00",
          "currency_code": "USD"
        }
      },
      "discounted_price": "10.00",
      "discounted_price_set": {
        "shop_money": {
          "amount": "10.00",
          "currency_code": "USD"
        },
        "presentment_money": {
          "amount": "10.00",
          "currency_code": "USD"
        }
      },
      "is_removed": false,
      "phone": null,
      "price": "10.00",
      "price_set": {
        "shop_money": {
          "amount": "10.00",
          "currency_code": "USD"
        },
        "presentment_money": {
          "amount": "10.00",
          "currency_code": "USD"
        }
      },
      "requested_fulfillment_service_id": null,
      "source": "shopify",
      "title": "Generic Shipping",
      "tax_lines": [],
      "discount_allocations": []
    }
  ],
  "returns": []
}';

        Bus::fake();

        $response = $this->postJson('/!/shopify/webhook/order', json_decode($payload, true));

        Bus::assertDispatched(ImportSingleProductJob::class, function ($job) {
            return $job->productId == 788032119674292922;
        });

        Bus::assertDispatched(ImportSingleProductJob::class, function ($job) {
            return $job->productId == 788032119674292923;
        });

        Bus::assertDispatched(ImportSingleProductJob::class, function ($job) {
            return $job->productId == 788032119674292924;
        });

        $this->assertSame('[]', $response->getContent());
    }
}
