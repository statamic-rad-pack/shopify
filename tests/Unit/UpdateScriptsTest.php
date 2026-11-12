<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;
use StatamicRadPack\Shopify\UpdateScripts;

class UpdateScriptsTest extends TestCase
{
    #[Test]
    public function updates_from_five_to_six()
    {
        $oldContents = Facades\YAML::parse("title: Variant
tabs:
  main:
    display: Main
    sections:
      -
        fields:
          -
            handle: title
            field:
              type: text
              required: true
              validate:
                - required
          -
            handle: slug
            field:
              type: disabled_text
              localizable: true
              validate:
                - required
                - 'new \Statamic\Rules\UniqueEntryValue({collection}, {id}, {site})'
              display: 'Variant ID'
              width: 50
          -
            handle: sku
            field:
              display: SKU
              type: disabled_text
              width: 50
          -
            handle: product_slug
            field:
              type: hidden
              display: 'Product Slug'
          -
            handle: price
            field:
              display: Price
              type: disabled_text
              validate:
                - required
              width: 50
          -
            handle: compare_at_price
            field:
              display: 'Compare At Price'
              type: disabled_text
              width: 50
          -
            handle: inventory_quantity
            field:
              type: disabled_text
              display: Stock
              validate:
                - nullable
                - numeric
              width: 50
          -
            handle: grams
            field:
              display: 'Weight (Grams)'
              type: disabled_text
              width: 50
          -
            handle: option1
            field:
              display: 'Option 1'
              type: disabled_text
              width: 33
          -
            handle: option2
            field:
              display: 'Option 2'
              type: disabled_text
              width: 33
          -
            handle: option3
            field:
              display: 'Option 3'
              type: disabled_text
              width: 33
          -
            handle: requires_shipping
            field:
              display: 'Requires Shipping'
              type: toggle
          -
            handle: storefront_id
            field:
              display: 'Storefront ID'
              type: hidden
          -
            handle: inventory_policy
            field:
              display: 'Inventory Policy'
              type: hidden
          -
            handle: inventory_management
            field:
              display: 'Inventory Management'
              type: hidden
          -
            handle: image
            field:
              mode: grid
              display: Image
              type: assets
              container: assets
              max_files: 1
              listable: true
");

        $blueprint = Facades\Collection::find('variants')
            ->entryBlueprint();

        $blueprint
            ->setContents($oldContents)
            ->save();

        $this->assertCount(16, $blueprint->fields()->all());

        (new UpdateScripts\UpdateFromFiveToSix('statamic-rad-pack/shopify'))->update();

        $this->assertCount(19, $blueprint->fields()->all());
    }

    #[Test]
    public function adds_the_new_pricing_inventory_and_import_fields_to_existing_product_blueprints()
    {
        $blueprint = Facades\Collection::find(config('shopify.collection_handle', 'products'))->entryBlueprint();

        // an existing (pre-upgrade) blueprint: no Details tab, product_id in the
        // sidebar, and an in_stock field the site added itself
        $contents = Facades\YAML::file(__DIR__.'/../../resources/blueprints/product.yaml')->parse();
        unset($contents['tabs'][UpdateScripts\AddSyncFieldsToProductBlueprint::TAB]);
        $contents['tabs']['sidebar']['sections'][0]['fields'][] = ['handle' => 'product_id', 'field' => ['type' => 'disabled_text']];
        $contents['tabs']['main']['sections'][0]['fields'][] = ['handle' => 'in_stock', 'field' => ['type' => 'toggle', 'display' => 'Available']];
        $blueprint->setContents($contents)->save();

        (new UpdateScripts\AddSyncFieldsToProductBlueprint('statamic-rad-pack/shopify'))->update();

        $blueprint = Facades\Collection::find(config('shopify.collection_handle', 'products'))->entryBlueprint();
        $tab = $blueprint->contents()['tabs'][UpdateScripts\AddSyncFieldsToProductBlueprint::TAB];

        $this->assertSame('Details', $tab['display']);
        $this->assertSame(
            ['Pricing and inventory' => ['on_sale', 'price_min', 'price_max'], 'Shopify record' => ['shopify_synced_at']],
            collect($tab['sections'])->mapWithKeys(fn ($section) => [$section['display'] => array_column($section['fields'], 'handle')])->all(),
            'Only the new fields the blueprint is missing are added, in their sections'
        );

        // the site's own fields stay where they were
        $this->assertSame('Available', $blueprint->field('in_stock')->display());
        $this->assertContains('product_id', array_column($blueprint->contents()['tabs']['sidebar']['sections'][0]['fields'], 'handle'));
        $this->assertSame('read_only', $blueprint->field('price_min')->get('visibility'));

        // running it again changes nothing
        (new UpdateScripts\AddSyncFieldsToProductBlueprint('statamic-rad-pack/shopify'))->update();

        $tab = Facades\Collection::find(config('shopify.collection_handle', 'products'))->entryBlueprint()->contents()['tabs'][UpdateScripts\AddSyncFieldsToProductBlueprint::TAB];
        $this->assertCount(2, $tab['sections']);
    }

    #[Test]
    public function the_default_product_blueprint_keeps_shopify_fields_out_of_the_sidebar()
    {
        $contents = Facades\YAML::file(__DIR__.'/../../resources/blueprints/product.yaml')->parse();

        $this->assertSame(['slug'], array_column($contents['tabs']['sidebar']['sections'][0]['fields'], 'handle'));

        $shopifyFields = collect($contents['tabs']['shopify']['sections'])->flatMap(fn ($section) => array_column($section['fields'], 'handle'))->all();

        foreach (['product_id', 'published_at', 'vendor', 'type', 'tags', 'in_stock', 'on_sale', 'price_min', 'price_max', 'shopify_synced_at'] as $handle) {
            $this->assertContains($handle, $shopifyFields);
        }
    }
}
