<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Statamic\Facades;
use StatamicRadPack\Shopify\Tests\TestCase;

class MetafieldsTest extends TestCase
{
    /** @test */
    public function parses_metafields_correctly()
    {
        $fields = app(config('shopify.metafields_parser'))->execute([
            [
              'id' =>  1069228992,
              'namespace' =>  'my_fields',
              'key' =>  'sponsor',
              'value' =>  'Shopify',
              'description' =>  null,
              'owner_id' =>  382285388,
              'created_at' =>  '2023-07-11T18:10:28-04:00',
              'updated_at' =>  '2023-07-11T18:10:28-04:00',
              'owner_resource' =>  'blog',
              'type' =>  'single_line_text_field',
              'admin_graphql_api_id' =>  'gid://shopify/Metafield/1069228992'
            ]
        ], 'product');

        $this->assertSame(['sponsor' => 'Shopify'], $fields);
    }
}
