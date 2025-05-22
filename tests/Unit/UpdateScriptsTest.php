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
}
