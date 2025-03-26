<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Rest;
use Shopify\Clients\RestResponse;
use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;

class TagsTest extends TestCase
{
    private function tag($tag, $variables = [])
    {
        return (string) Facades\Parse::template($tag, $variables);
    }

    #[Test]
    public function outputs_shopify_tokens()
    {
        config()->set('shopify.url', 'abcd');
        config()->set('shopify.storefront_token', '1234');

        $this->assertEquals(str_replace(["\r", "\n"], '', "<script>
window.shopifyConfig = { url: 'abcd', token: '1234', apiVersion: '2024-07' };
</script>"),
            str_replace(["\r", "\n"], '', $this->tag('{{ shopify:tokens }}'))
        );
    }

    #[Test]
    public function outputs_product_price()
    {
        $product = Facades\Entry::make()->data([
            'title' => 'Obi wan',
            'vendor' => 'Kenobe',
            'slug' => 'obi-wan',
            'product_id' => 1,
        ])
            ->collection(config('shopify.collection_handle'));

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
            'inventory_quantity' => 0,
        ])->save();

        $this->assertEquals('Out of Stock', $this->tag('{{ shopify:product_price }}', ['slug' => 'obi-wan']));

        $variant2 = Facades\Entry::make()->data([
            'title' => 'Another T-shirt',
            'slug' => 'obi-wan-tshirt-2',
            'sku' => 'obi-wan-tshirt-2',
            'product_slug' => 'obi-wan',
            'price' => 10.99,
            'inventory_quantity' => 5,
            'storefront_id' => 'def',
        ])
            ->collection('variants');

        $variant2->save();

        $this->assertEquals('From £9.99', $this->tag('{{ shopify:product_price show_from="true" }}', ['slug' => 'obi-wan']));

    }

    #[Test]
    public function outputs_in_stock()
    {
        $product = Facades\Entry::make()->data([
            'title' => 'Obi wan',
            'vendor' => 'Kenobe',
            'slug' => 'obi-wan',
            'product_id' => 1,
        ])
            ->collection(config('shopify.collection_handle'));

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
            'inventory_quantity' => 0,
        ])->save();

        $this->assertEquals('', $this->tag('{{ if {shopify:in_stock} }}Yes{{ /if }}', ['slug' => 'obi-wan']));
    }

    #[Test]
    public function outputs_product_variants_generate()
    {
        $product = Facades\Entry::make()->data([
            'title' => 'Obi wan',
            'vendor' => 'Kenobe',
            'slug' => 'obi-wan',
            'product_id' => 1,
        ])
            ->collection(config('shopify.collection_handle'));

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

        $this->assertEquals('<input type="hidden" name="ss-product-variant" value="obi-wan-tshirt">', trim($tagOutput));

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

        $this->assertEquals('<select name="ss-product-variant" class="ss-variant-select "><option value="obi-wan-tshirt" data-in-stock="true">T-shirt - £9.99</option><option value="obi-wan-tshirt-2" data-in-stock="true">Another T-shirt - £10.99</option></select>', $tagOutput);

        $variant->merge([
            'inventory_quantity' => 0,
        ])->save();

        $tagOutput = $this->tag('{{ shopify:variants:generate show_price="true" show_out_of_stock="false" }}', ['slug' => 'obi-wan']);
        $tagOutput = str_replace(["\r", "\n", "\t"], '', $tagOutput);
        $tagOutput = preg_replace('/\>\s+\</m', '><', trim($tagOutput));

        $this->assertEquals('<select name="ss-product-variant" class="ss-variant-select "><option value="obi-wan-tshirt" data-in-stock="false" disabled>T-shirt - £9.99</option><option value="obi-wan-tshirt-2" data-in-stock="true">Another T-shirt - £10.99</option></select>', $tagOutput);

    }

    #[Test]
    public function outputs_product_variants()
    {
        $product = Facades\Entry::make()->data([
            'title' => 'Obi wan',
            'vendor' => 'Kenobe',
            'slug' => 'obi-wan',
            'product_id' => 1,
        ])
            ->collection(config('shopify.collection_handle'));

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

    #[Test]
    public function outputs_an_address_form()
    {
        $this->assertEquals('<form method="POST" action="http://localhost/!/shopify/address"><input type="hidden" name="_token" value="">Some content</form>', str_replace(' autocomplete="off"', '', $this->tag('{{ shopify:address_form }}Some content{{ /shopify:address_form }}')));

        $this->assertEquals('<form method="POST" action="http://localhost/!/shopify/address/1"><input type="hidden" name="_token" value="">Some content</form>', str_replace(' autocomplete="off"', '', $this->tag('{{ shopify:address_form address_id="1" }}Some content{{ /shopify:address_form }}')));

        $this->assertEquals('<form method="POST" action="http://localhost/!/shopify/address"><input type="hidden" name="_token" value=""><input type="hidden" name="customer_id" value="1" />Some content</form>', str_replace(' autocomplete="off"', '', $this->tag('{{ shopify:address_form customer_id="1"  }}Some content{{ /shopify:address_form }}')));
    }

    #[Test]
    public function outputs_a_customer()
    {
        $this->mock(Rest::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{"customer":{"id": 706405506930370000,"email": "bob@biller.com","accepts_marketing": true,"created_at": "2021-12-31T19:00:00-05:00","updated_at": "2021-12-31T19:00:00-05:00","first_name": "Bob","last_name": "Biller","orders_count": 0,"state": "disabled","total_spent": "0.00","last_order_id": null,"note": "This customer loves ice cream","verified_email": true,"multipass_identifier": null,"tax_exempt": false,"tags": "","last_order_name": null,"currency": "USD","phone": null,"addresses": [],"accepts_marketing_updated_at": "2021-12-31T19:00:00-05:00","marketing_opt_in_level": null,"tax_exemptions": [],"email_marketing_consent": null,"sms_marketing_consent": null,"admin_graphql_api_id": "gid://shopify/Customer/706405506930370084"}}'
                ));
        });

        $user = tap(Facades\User::make()
            ->email('test@test.com')
            ->data([
                'name' => 'This name',
                'shopify_id' => '706405506930370000',
            ])
        )->save();

        $this->assertEquals('706405506930370000 - bob@biller.com', $this->tag('{{ shopify:customer customer_id="706405506930370000" }}{{ shopify_id }} - {{ email }}{{ /shopify:customer }}'));

        $this->assertEquals('yes', $this->tag('{{ shopify:customer customer_id="706405506930370001" }}{{ if not_found }}yes{{ /if }}{{ /shopify:customer }}'));

        $this->actingAs($user);
        $this->assertEquals('706405506930370000 - bob@biller.com', $this->tag('{{ shopify:customer }}{{ shopify_id }} - {{ email }}{{ /shopify:customer }}'));
    }

    #[Test]
    public function outputs_customer_addresses()
    {
        $this->mock(Rest::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{
                      "addresses": [
                        {
                          "id": 207119551,
                          "customer_id": 706405506930370000,
                          "first_name": null,
                          "last_name": null,
                          "company": null,
                          "address1": "Chestnut Street 92",
                          "address2": "",
                          "city": "Louisville",
                          "province": "Kentucky",
                          "country": "United States",
                          "zip": "40202",
                          "phone": "555-625-1199",
                          "name": "",
                          "province_code": "KY",
                          "country_code": "US",
                          "country_name": "United States",
                          "default": true
                        }
                      ]
                    }'
                ));
        });

        $user = tap(Facades\User::make()
            ->email('test@test.com')
            ->data([
                'name' => 'This name',
                'shopify_id' => '706405506930370000',
            ])
        )->save();

        $this->assertEquals('207119551 - Chestnut Street 92', $this->tag('{{ shopify:customer:addresses customer_id="706405506930370000" }}{{ addresses }}{{ id }} - {{ address1 }}{{ /addresses }}{{ /shopify:customer:addresses }}'));

        $this->assertEquals('0', $this->tag('{{ shopify:customer:addresses customer_id="706405506930370001" }}{{ addresses_count }}{{ /shopify:customer:addresses }}'));

        $this->actingAs($user);
        $this->assertEquals('207119551 - Chestnut Street 92', $this->tag('{{ shopify:customer:addresses }}{{ addresses }}{{ id }} - {{ address1 }}{{ /addresses }}{{ /shopify:customer:addresses }}'));
    }

    #[Test]
    public function outputs_customer_orders()
    {
        $this->mock(Rest::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{
                      "orders": [
                        {
                          "id": 450789469,
                          "admin_graphql_api_id": "gid://shopify/Order/450789469",
                          "app_id": null,
                          "browser_ip": "0.0.0.0",
                          "buyer_accepts_marketing": false,
                          "cancel_reason": null,
                          "cancelled_at": null,
                          "cart_token": "68778783ad298f1c80c3bafcddeea02f",
                          "checkout_id": 901414060,
                          "checkout_token": "bd5a8aa1ecd019dd3520ff791ee3a24c",
                          "client_details": {
                            "accept_language": null,
                            "browser_height": null,
                            "browser_ip": "0.0.0.0",
                            "browser_width": null,
                            "session_hash": null,
                            "user_agent": null
                          },
                          "closed_at": null,
                          "confirmation_number": null,
                          "confirmed": true,
                          "contact_email": "bob.norman@mail.example.com",
                          "created_at": "2008-01-10T11:00:00-05:00",
                          "currency": "USD",
                          "current_subtotal_price": "195.67",
                          "current_subtotal_price_set": {
                            "shop_money": {
                              "amount": "195.67",
                              "currency_code": "USD"
                            },
                            "presentment_money": {
                              "amount": "195.67",
                              "currency_code": "USD"
                            }
                          },
                          "current_total_additional_fees_set": null,
                          "current_total_discounts": "3.33",
                          "current_total_discounts_set": {
                            "shop_money": {
                              "amount": "3.33",
                              "currency_code": "USD"
                            },
                            "presentment_money": {
                              "amount": "3.33",
                              "currency_code": "USD"
                            }
                          },
                          "current_total_duties_set": null,
                          "current_total_price": "199.65",
                          "current_total_price_set": {
                            "shop_money": {
                              "amount": "199.65",
                              "currency_code": "USD"
                            },
                            "presentment_money": {
                              "amount": "199.65",
                              "currency_code": "USD"
                            }
                          },
                          "current_total_tax": "3.98",
                          "current_total_tax_set": {
                            "shop_money": {
                              "amount": "3.98",
                              "currency_code": "USD"
                            },
                            "presentment_money": {
                              "amount": "3.98",
                              "currency_code": "USD"
                            }
                          },
                          "customer_locale": null,
                          "device_id": null,
                          "discount_codes": [
                            {
                              "code": "TENOFF",
                              "amount": "10.00",
                              "type": "fixed_amount"
                            }
                          ],
                          "email": "bob.norman@mail.example.com",
                          "estimated_taxes": false,
                          "financial_status": "partially_refunded",
                          "fulfillment_status": null,
                          "landing_site": "http://www.example.com?source=abc",
                          "landing_site_ref": "abc",
                          "location_id": null,
                          "merchant_of_record_app_id": null,
                          "name": "#1001",
                          "note": null,
                          "note_attributes": [
                            {
                              "name": "custom engraving",
                              "value": "Happy Birthday"
                            },
                            {
                              "name": "colour",
                              "value": "green"
                            }
                          ],
                          "number": 1,
                          "order_number": 1001,
                          "order_status_url": "https://jsmith.myshopify.com/548380009/orders/b1946ac92492d2347c6235b4d2611184/authenticate?key=imasecretipod",
                          "original_total_additional_fees_set": null,
                          "original_total_duties_set": null,
                          "payment_gateway_names": [
                            "bogus"
                          ],
                          "phone": "+557734881234",
                          "po_number": "ABC123",
                          "presentment_currency": "USD",
                          "processed_at": "2008-01-10T11:00:00-05:00",
                          "reference": "fhwdgads",
                          "referring_site": "http://www.otherexample.com",
                          "source_identifier": "fhwdgads",
                          "source_name": "web",
                          "source_url": null,
                          "subtotal_price": "597.00",
                          "subtotal_price_set": {
                            "shop_money": {
                              "amount": "597.00",
                              "currency_code": "USD"
                            },
                            "presentment_money": {
                              "amount": "597.00",
                              "currency_code": "USD"
                            }
                          },
                          "tags": "",
                          "tax_exempt": false,
                          "tax_lines": [
                            {
                              "price": "11.94",
                              "rate": 0.06,
                              "title": "State Tax",
                              "price_set": {
                                "shop_money": {
                                  "amount": "11.94",
                                  "currency_code": "USD"
                                },
                                "presentment_money": {
                                  "amount": "11.94",
                                  "currency_code": "USD"
                                }
                              },
                              "channel_liable": null
                            }
                          ],
                          "taxes_included": false,
                          "test": false,
                          "token": "b1946ac92492d2347c6235b4d2611184",
                          "total_discounts": "10.00",
                          "total_discounts_set": {
                            "shop_money": {
                              "amount": "10.00",
                              "currency_code": "USD"
                            },
                            "presentment_money": {
                              "amount": "10.00",
                              "currency_code": "USD"
                            }
                          },
                          "total_line_items_price": "597.00",
                          "total_line_items_price_set": {
                            "shop_money": {
                              "amount": "597.00",
                              "currency_code": "USD"
                            },
                            "presentment_money": {
                              "amount": "597.00",
                              "currency_code": "USD"
                            }
                          },
                          "total_outstanding": "0.00",
                          "total_price": "598.94",
                          "total_price_set": {
                            "shop_money": {
                              "amount": "598.94",
                              "currency_code": "USD"
                            },
                            "presentment_money": {
                              "amount": "598.94",
                              "currency_code": "USD"
                            }
                          },
                          "total_shipping_price_set": {
                            "shop_money": {
                              "amount": "0.00",
                              "currency_code": "USD"
                            },
                            "presentment_money": {
                              "amount": "0.00",
                              "currency_code": "USD"
                            }
                          },
                          "total_tax": "11.94",
                          "total_tax_set": {
                            "shop_money": {
                              "amount": "11.94",
                              "currency_code": "USD"
                            },
                            "presentment_money": {
                              "amount": "11.94",
                              "currency_code": "USD"
                            }
                          },
                          "total_tip_received": "0.00",
                          "total_weight": 0,
                          "updated_at": "2008-01-10T11:00:00-05:00",
                          "user_id": null,
                          "billing_address": {
                            "first_name": "Bob",
                            "address1": "Chestnut Street 92",
                            "phone": "+1(502)-459-2181",
                            "city": "Louisville",
                            "zip": "40202",
                            "province": "Kentucky",
                            "country": "United States",
                            "last_name": "Norman",
                            "address2": "",
                            "company": null,
                            "latitude": 45.41634,
                            "longitude": -75.6868,
                            "name": "Bob Norman",
                            "country_code": "US",
                            "province_code": "KY"
                          },
                          "customer": {
                            "id": 207119551,
                            "email": "bob.norman@mail.example.com",
                            "accepts_marketing": false,
                            "created_at": "2023-10-03T13:42:12-04:00",
                            "updated_at": "2023-10-03T13:42:12-04:00",
                            "first_name": "Bob",
                            "last_name": "Norman",
                            "state": "disabled",
                            "note": null,
                            "verified_email": true,
                            "multipass_identifier": null,
                            "tax_exempt": false,
                            "phone": "+16136120707",
                            "email_marketing_consent": {
                              "state": "not_subscribed",
                              "opt_in_level": null,
                              "consent_updated_at": "2004-06-13T11:57:11-04:00"
                            },
                            "sms_marketing_consent": {
                              "state": "not_subscribed",
                              "opt_in_level": "single_opt_in",
                              "consent_updated_at": "2023-10-03T13:42:12-04:00",
                              "consent_collected_from": "OTHER"
                            },
                            "tags": "Léon, Noël",
                            "currency": "USD",
                            "accepts_marketing_updated_at": "2005-06-12T11:57:11-04:00",
                            "marketing_opt_in_level": null,
                            "tax_exemptions": [],
                            "admin_graphql_api_id": "gid://shopify/Customer/207119551",
                            "default_address": {
                              "id": 207119551,
                              "customer_id": 207119551,
                              "first_name": null,
                              "last_name": null,
                              "company": null,
                              "address1": "Chestnut Street 92",
                              "address2": "",
                              "city": "Louisville",
                              "province": "Kentucky",
                              "country": "United States",
                              "zip": "40202",
                              "phone": "555-625-1199",
                              "name": "",
                              "province_code": "KY",
                              "country_code": "US",
                              "country_name": "United States",
                              "default": true
                            }
                          },
                          "discount_applications": [
                            {
                              "target_type": "line_item",
                              "type": "discount_code",
                              "value": "10.0",
                              "value_type": "fixed_amount",
                              "allocation_method": "across",
                              "target_selection": "all",
                              "code": "TENOFF"
                            }
                          ],
                          "fulfillments": [
                            {
                              "id": 255858046,
                              "admin_graphql_api_id": "gid://shopify/Fulfillment/255858046",
                              "created_at": "2023-10-03T13:42:12-04:00",
                              "location_id": 655441491,
                              "name": "#1001.0",
                              "order_id": 450789469,
                              "origin_address": {},
                              "receipt": {
                                "testcase": true,
                                "authorization": "123456"
                              },
                              "service": "manual",
                              "shipment_status": null,
                              "status": "failure",
                              "tracking_company": "USPS",
                              "tracking_number": "1Z2345",
                              "tracking_numbers": [
                                "1Z2345"
                              ],
                              "tracking_url": "https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=1Z2345",
                              "tracking_urls": [
                                "https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=1Z2345"
                              ],
                              "updated_at": "2023-10-03T13:42:12-04:00",
                              "line_items": [
                                {
                                  "id": 466157049,
                                  "admin_graphql_api_id": "gid://shopify/LineItem/466157049",
                                  "fulfillable_quantity": 0,
                                  "fulfillment_service": "manual",
                                  "fulfillment_status": null,
                                  "gift_card": false,
                                  "grams": 200,
                                  "name": "IPod Nano - 8gb - green",
                                  "price": "199.00",
                                  "price_set": {
                                    "shop_money": {
                                      "amount": "199.00",
                                      "currency_code": "USD"
                                    },
                                    "presentment_money": {
                                      "amount": "199.00",
                                      "currency_code": "USD"
                                    }
                                  },
                                  "product_exists": true,
                                  "product_id": 632910392,
                                  "properties": [
                                    {
                                      "name": "Custom Engraving Front",
                                      "value": "Happy Birthday"
                                    },
                                    {
                                      "name": "Custom Engraving Back",
                                      "value": "Merry Christmas"
                                    }
                                  ],
                                  "quantity": 1,
                                  "requires_shipping": true,
                                  "sku": "IPOD2008GREEN",
                                  "taxable": true,
                                  "title": "IPod Nano - 8gb",
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
                                  "variant_id": 39072856,
                                  "variant_inventory_management": "shopify",
                                  "variant_title": "green",
                                  "vendor": null,
                                  "tax_lines": [
                                    {
                                      "channel_liable": null,
                                      "price": "3.98",
                                      "price_set": {
                                        "shop_money": {
                                          "amount": "3.98",
                                          "currency_code": "USD"
                                        },
                                        "presentment_money": {
                                          "amount": "3.98",
                                          "currency_code": "USD"
                                        }
                                      },
                                      "rate": 0.06,
                                      "title": "State Tax"
                                    }
                                  ],
                                  "duties": [],
                                  "discount_allocations": [
                                    {
                                      "amount": "3.34",
                                      "amount_set": {
                                        "shop_money": {
                                          "amount": "3.34",
                                          "currency_code": "USD"
                                        },
                                        "presentment_money": {
                                          "amount": "3.34",
                                          "currency_code": "USD"
                                        }
                                      },
                                      "discount_application_index": 0
                                    }
                                  ]
                                }
                              ]
                            }
                          ],
                          "line_items": [
                            {
                              "id": 466157049,
                              "admin_graphql_api_id": "gid://shopify/LineItem/466157049",
                              "fulfillable_quantity": 0,
                              "fulfillment_service": "manual",
                              "fulfillment_status": null,
                              "gift_card": false,
                              "grams": 200,
                              "name": "IPod Nano - 8gb - green",
                              "price": "199.00",
                              "price_set": {
                                "shop_money": {
                                  "amount": "199.00",
                                  "currency_code": "USD"
                                },
                                "presentment_money": {
                                  "amount": "199.00",
                                  "currency_code": "USD"
                                }
                              },
                              "product_exists": true,
                              "product_id": 632910392,
                              "properties": [
                                {
                                  "name": "Custom Engraving Front",
                                  "value": "Happy Birthday"
                                },
                                {
                                  "name": "Custom Engraving Back",
                                  "value": "Merry Christmas"
                                }
                              ],
                              "quantity": 1,
                              "requires_shipping": true,
                              "sku": "IPOD2008GREEN",
                              "taxable": true,
                              "title": "IPod Nano - 8gb",
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
                              "variant_id": 39072856,
                              "variant_inventory_management": "shopify",
                              "variant_title": "green",
                              "vendor": null,
                              "tax_lines": [
                                {
                                  "channel_liable": null,
                                  "price": "3.98",
                                  "price_set": {
                                    "shop_money": {
                                      "amount": "3.98",
                                      "currency_code": "USD"
                                    },
                                    "presentment_money": {
                                      "amount": "3.98",
                                      "currency_code": "USD"
                                    }
                                  },
                                  "rate": 0.06,
                                  "title": "State Tax"
                                }
                              ],
                              "duties": [],
                              "discount_allocations": [
                                {
                                  "amount": "3.34",
                                  "amount_set": {
                                    "shop_money": {
                                      "amount": "3.34",
                                      "currency_code": "USD"
                                    },
                                    "presentment_money": {
                                      "amount": "3.34",
                                      "currency_code": "USD"
                                    }
                                  },
                                  "discount_application_index": 0
                                }
                              ]
                            },
                            {
                              "id": 518995019,
                              "admin_graphql_api_id": "gid://shopify/LineItem/518995019",
                              "fulfillable_quantity": 1,
                              "fulfillment_service": "manual",
                              "fulfillment_status": null,
                              "gift_card": false,
                              "grams": 200,
                              "name": "IPod Nano - 8gb - red",
                              "price": "199.00",
                              "price_set": {
                                "shop_money": {
                                  "amount": "199.00",
                                  "currency_code": "USD"
                                },
                                "presentment_money": {
                                  "amount": "199.00",
                                  "currency_code": "USD"
                                }
                              },
                              "product_exists": true,
                              "product_id": 632910392,
                              "properties": [],
                              "quantity": 1,
                              "requires_shipping": true,
                              "sku": "IPOD2008RED",
                              "taxable": true,
                              "title": "IPod Nano - 8gb",
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
                              "variant_id": 49148385,
                              "variant_inventory_management": "shopify",
                              "variant_title": "red",
                              "vendor": null,
                              "tax_lines": [
                                {
                                  "channel_liable": null,
                                  "price": "3.98",
                                  "price_set": {
                                    "shop_money": {
                                      "amount": "3.98",
                                      "currency_code": "USD"
                                    },
                                    "presentment_money": {
                                      "amount": "3.98",
                                      "currency_code": "USD"
                                    }
                                  },
                                  "rate": 0.06,
                                  "title": "State Tax"
                                }
                              ],
                              "duties": [],
                              "discount_allocations": [
                                {
                                  "amount": "3.33",
                                  "amount_set": {
                                    "shop_money": {
                                      "amount": "3.33",
                                      "currency_code": "USD"
                                    },
                                    "presentment_money": {
                                      "amount": "3.33",
                                      "currency_code": "USD"
                                    }
                                  },
                                  "discount_application_index": 0
                                }
                              ]
                            },
                            {
                              "id": 703073504,
                              "admin_graphql_api_id": "gid://shopify/LineItem/703073504",
                              "fulfillable_quantity": 0,
                              "fulfillment_service": "manual",
                              "fulfillment_status": null,
                              "gift_card": false,
                              "grams": 200,
                              "name": "IPod Nano - 8gb - black",
                              "price": "199.00",
                              "price_set": {
                                "shop_money": {
                                  "amount": "199.00",
                                  "currency_code": "USD"
                                },
                                "presentment_money": {
                                  "amount": "199.00",
                                  "currency_code": "USD"
                                }
                              },
                              "product_exists": true,
                              "product_id": 632910392,
                              "properties": [],
                              "quantity": 1,
                              "requires_shipping": true,
                              "sku": "IPOD2008BLACK",
                              "taxable": true,
                              "title": "IPod Nano - 8gb",
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
                              "variant_id": 457924702,
                              "variant_inventory_management": "shopify",
                              "variant_title": "black",
                              "vendor": null,
                              "tax_lines": [
                                {
                                  "channel_liable": null,
                                  "price": "3.98",
                                  "price_set": {
                                    "shop_money": {
                                      "amount": "3.98",
                                      "currency_code": "USD"
                                    },
                                    "presentment_money": {
                                      "amount": "3.98",
                                      "currency_code": "USD"
                                    }
                                  },
                                  "rate": 0.06,
                                  "title": "State Tax"
                                }
                              ],
                              "duties": [],
                              "discount_allocations": [
                                {
                                  "amount": "3.33",
                                  "amount_set": {
                                    "shop_money": {
                                      "amount": "3.33",
                                      "currency_code": "USD"
                                    },
                                    "presentment_money": {
                                      "amount": "3.33",
                                      "currency_code": "USD"
                                    }
                                  },
                                  "discount_application_index": 0
                                }
                              ]
                            }
                          ],
                          "payment_terms": null,
                          "refunds": [
                            {
                              "id": 509562969,
                              "admin_graphql_api_id": "gid://shopify/Refund/509562969",
                              "created_at": "2023-10-03T13:42:12-04:00",
                              "note": "it broke during shipping",
                              "order_id": 450789469,
                              "processed_at": "2023-10-03T13:42:12-04:00",
                              "restock": true,
                              "total_additional_fees_set": {
                                "shop_money": {
                                  "amount": "0.00",
                                  "currency_code": "USD"
                                },
                                "presentment_money": {
                                  "amount": "0.00",
                                  "currency_code": "USD"
                                }
                              },
                              "total_duties_set": {
                                "shop_money": {
                                  "amount": "0.00",
                                  "currency_code": "USD"
                                },
                                "presentment_money": {
                                  "amount": "0.00",
                                  "currency_code": "USD"
                                }
                              },
                              "user_id": 548380009,
                              "order_adjustments": [],
                              "transactions": [
                                {
                                  "id": 179259969,
                                  "admin_graphql_api_id": "gid://shopify/OrderTransaction/179259969",
                                  "amount": "209.00",
                                  "authorization": "authorization-key",
                                  "created_at": "2005-08-05T12:59:12-04:00",
                                  "currency": "USD",
                                  "device_id": null,
                                  "error_code": null,
                                  "gateway": "bogus",
                                  "kind": "refund",
                                  "location_id": null,
                                  "message": null,
                                  "order_id": 450789469,
                                  "parent_id": 801038806,
                                  "payment_id": "#1001.3",
                                  "processed_at": "2005-08-05T12:59:12-04:00",
                                  "receipt": {},
                                  "source_name": "web",
                                  "status": "success",
                                  "test": false,
                                  "user_id": null
                                }
                              ],
                              "refund_line_items": [
                                {
                                  "id": 104689539,
                                  "line_item_id": 703073504,
                                  "location_id": 487838322,
                                  "quantity": 1,
                                  "restock_type": "legacy_restock",
                                  "subtotal": 195.66,
                                  "subtotal_set": {
                                    "shop_money": {
                                      "amount": "195.66",
                                      "currency_code": "USD"
                                    },
                                    "presentment_money": {
                                      "amount": "195.66",
                                      "currency_code": "USD"
                                    }
                                  },
                                  "total_tax": 3.98,
                                  "total_tax_set": {
                                    "shop_money": {
                                      "amount": "3.98",
                                      "currency_code": "USD"
                                    },
                                    "presentment_money": {
                                      "amount": "3.98",
                                      "currency_code": "USD"
                                    }
                                  },
                                  "line_item": {
                                    "id": 703073504,
                                    "admin_graphql_api_id": "gid://shopify/LineItem/703073504",
                                    "fulfillable_quantity": 0,
                                    "fulfillment_service": "manual",
                                    "fulfillment_status": null,
                                    "gift_card": false,
                                    "grams": 200,
                                    "name": "IPod Nano - 8gb - black",
                                    "price": "199.00",
                                    "price_set": {
                                      "shop_money": {
                                        "amount": "199.00",
                                        "currency_code": "USD"
                                      },
                                      "presentment_money": {
                                        "amount": "199.00",
                                        "currency_code": "USD"
                                      }
                                    },
                                    "product_exists": true,
                                    "product_id": 632910392,
                                    "properties": [],
                                    "quantity": 1,
                                    "requires_shipping": true,
                                    "sku": "IPOD2008BLACK",
                                    "taxable": true,
                                    "title": "IPod Nano - 8gb",
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
                                    "variant_id": 457924702,
                                    "variant_inventory_management": "shopify",
                                    "variant_title": "black",
                                    "vendor": null,
                                    "tax_lines": [
                                      {
                                        "channel_liable": null,
                                        "price": "3.98",
                                        "price_set": {
                                          "shop_money": {
                                            "amount": "3.98",
                                            "currency_code": "USD"
                                          },
                                          "presentment_money": {
                                            "amount": "3.98",
                                            "currency_code": "USD"
                                          }
                                        },
                                        "rate": 0.06,
                                        "title": "State Tax"
                                      }
                                    ],
                                    "duties": [],
                                    "discount_allocations": [
                                      {
                                        "amount": "3.33",
                                        "amount_set": {
                                          "shop_money": {
                                            "amount": "3.33",
                                            "currency_code": "USD"
                                          },
                                          "presentment_money": {
                                            "amount": "3.33",
                                            "currency_code": "USD"
                                          }
                                        },
                                        "discount_application_index": 0
                                      }
                                    ]
                                  }
                                },
                                {
                                  "id": 709875399,
                                  "line_item_id": 466157049,
                                  "location_id": 487838322,
                                  "quantity": 1,
                                  "restock_type": "legacy_restock",
                                  "subtotal": 195.67,
                                  "subtotal_set": {
                                    "shop_money": {
                                      "amount": "195.67",
                                      "currency_code": "USD"
                                    },
                                    "presentment_money": {
                                      "amount": "195.67",
                                      "currency_code": "USD"
                                    }
                                  },
                                  "total_tax": 3.98,
                                  "total_tax_set": {
                                    "shop_money": {
                                      "amount": "3.98",
                                      "currency_code": "USD"
                                    },
                                    "presentment_money": {
                                      "amount": "3.98",
                                      "currency_code": "USD"
                                    }
                                  },
                                  "line_item": {
                                    "id": 466157049,
                                    "admin_graphql_api_id": "gid://shopify/LineItem/466157049",
                                    "fulfillable_quantity": 0,
                                    "fulfillment_service": "manual",
                                    "fulfillment_status": null,
                                    "gift_card": false,
                                    "grams": 200,
                                    "name": "IPod Nano - 8gb - green",
                                    "price": "199.00",
                                    "price_set": {
                                      "shop_money": {
                                        "amount": "199.00",
                                        "currency_code": "USD"
                                      },
                                      "presentment_money": {
                                        "amount": "199.00",
                                        "currency_code": "USD"
                                      }
                                    },
                                    "product_exists": true,
                                    "product_id": 632910392,
                                    "properties": [
                                      {
                                        "name": "Custom Engraving Front",
                                        "value": "Happy Birthday"
                                      },
                                      {
                                        "name": "Custom Engraving Back",
                                        "value": "Merry Christmas"
                                      }
                                    ],
                                    "quantity": 1,
                                    "requires_shipping": true,
                                    "sku": "IPOD2008GREEN",
                                    "taxable": true,
                                    "title": "IPod Nano - 8gb",
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
                                    "variant_id": 39072856,
                                    "variant_inventory_management": "shopify",
                                    "variant_title": "green",
                                    "vendor": null,
                                    "tax_lines": [
                                      {
                                        "channel_liable": null,
                                        "price": "3.98",
                                        "price_set": {
                                          "shop_money": {
                                            "amount": "3.98",
                                            "currency_code": "USD"
                                          },
                                          "presentment_money": {
                                            "amount": "3.98",
                                            "currency_code": "USD"
                                          }
                                        },
                                        "rate": 0.06,
                                        "title": "State Tax"
                                      }
                                    ],
                                    "duties": [],
                                    "discount_allocations": [
                                      {
                                        "amount": "3.34",
                                        "amount_set": {
                                          "shop_money": {
                                            "amount": "3.34",
                                            "currency_code": "USD"
                                          },
                                          "presentment_money": {
                                            "amount": "3.34",
                                            "currency_code": "USD"
                                          }
                                        },
                                        "discount_application_index": 0
                                      }
                                    ]
                                  }
                                }
                              ],
                              "duties": [],
                              "additional_fees": []
                            }
                          ],
                          "shipping_address": {
                            "first_name": "Bob",
                            "address1": "Chestnut Street 92",
                            "phone": "+1(502)-459-2181",
                            "city": "Louisville",
                            "zip": "40202",
                            "province": "Kentucky",
                            "country": "United States",
                            "last_name": "Norman",
                            "address2": "",
                            "company": null,
                            "latitude": 45.41634,
                            "longitude": -75.6868,
                            "name": "Bob Norman",
                            "country_code": "US",
                            "province_code": "KY"
                          },
                          "shipping_lines": [
                            {
                              "id": 369256396,
                              "carrier_identifier": null,
                              "code": "Free Shipping",
                              "discounted_price": "0.00",
                              "discounted_price_set": {
                                "shop_money": {
                                  "amount": "0.00",
                                  "currency_code": "USD"
                                },
                                "presentment_money": {
                                  "amount": "0.00",
                                  "currency_code": "USD"
                                }
                              },
                              "phone": null,
                              "price": "0.00",
                              "price_set": {
                                "shop_money": {
                                  "amount": "0.00",
                                  "currency_code": "USD"
                                },
                                "presentment_money": {
                                  "amount": "0.00",
                                  "currency_code": "USD"
                                }
                              },
                              "requested_fulfillment_service_id": null,
                              "source": "shopify",
                              "title": "Free Shipping",
                              "tax_lines": [],
                              "discount_allocations": []
                            }
                          ]
                        }
                      ]
                    }'
                ));
        });

        $user = tap(Facades\User::make()
            ->email('test@test.com')
            ->data([
                'name' => 'This name',
                'shopify_id' => '706405506930370000',
            ])
        )->save();

        $this->assertEquals('450789469', $this->tag('{{ shopify:customer:orders customer_id="706405506930370000" }}{{ orders }}{{ id }}{{ /orders }}{{ /shopify:customer:orders }}'));

        $this->assertEquals('0', $this->tag('{{ shopify:customer:orders customer_id="706405506930370001" }}{{ orders_count }}{{ /shopify:customer:orders }}'));

        $this->actingAs($user);
        $this->assertEquals('450789469', $this->tag('{{ shopify:customer:orders }}{{ orders }}{{ id }}{{ /orders }}{{ /shopify:customer:orders }}'));

        $this->assertEquals('450789469', $this->tag('{{ shopify:customer:orders paginate="1" }}{{ orders }}{{ id }}{{ /orders }}{{ /shopify:customer:orders }}'));
        $this->assertEquals('1', $this->tag('{{ shopify:customer:orders paginate="1" }}{{ paginate:total_items }}{{ /shopify:customer:orders }}'));
    }
}
