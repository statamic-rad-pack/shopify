<?php

namespace StatamicRadPack\Shopify\Enums;

enum WebhookTopic: string
{
    case ProductsCreate = 'PRODUCTS_CREATE';
    case ProductsUpdate = 'PRODUCTS_UPDATE';
    case ProductsDelete = 'PRODUCTS_DELETE';
    case CustomersCreate = 'CUSTOMERS_CREATE';
    case CustomersUpdate = 'CUSTOMERS_UPDATE';
    case CustomersDelete = 'CUSTOMERS_DELETE';
    case OrdersCreate = 'ORDERS_CREATE';
    case CollectionsCreate = 'COLLECTIONS_CREATE';
    case CollectionsUpdate = 'COLLECTIONS_UPDATE';
    case CollectionsDelete = 'COLLECTIONS_DELETE';

    public function routeName(): string
    {
        return match ($this) {
            self::ProductsCreate => 'statamic.shopify.webhook.product.create',
            self::ProductsUpdate => 'statamic.shopify.webhook.product.update',
            self::ProductsDelete => 'statamic.shopify.webhook.product.delete',
            self::CustomersCreate => 'statamic.shopify.webhook.customer.create',
            self::CustomersUpdate => 'statamic.shopify.webhook.customer.update',
            self::CustomersDelete => 'statamic.shopify.webhook.customer.delete',
            self::OrdersCreate => 'statamic.shopify.webhook.order.created',
            self::CollectionsCreate => 'statamic.shopify.webhook.collection.create',
            self::CollectionsUpdate => 'statamic.shopify.webhook.collection.update',
            self::CollectionsDelete => 'statamic.shopify.webhook.collection.delete',
        };
    }

    public function callbackUrl(): string
    {
        return route($this->routeName(), [], true);
    }
}
