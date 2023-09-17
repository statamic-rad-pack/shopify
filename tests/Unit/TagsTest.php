<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Statamic\Facades\Parse;
use StatamicRadPack\Shopify\Tests\TestCase;

class TagsTest extends TestCase
{
    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    /** @test */
    public function outputs_shopify_tokens()
    {
        config()->set('shopify.url', 'abcd');
        config()->set('shopify.storefront_token', '1234');

        $this->assertEquals("
    <script>\r\n
    window.shopifyUrl = 'abcd';\r\n
    window.shopifyToken = '1234';\r\n
    </script>",
            $this->tag('{{ shopify:tokens }}')
        );
    }

    /** @test */
    public function outputs_shopify_scripts()
    {
        $this->assertStringStartsWith("<script", $this->tag('{{ shopify:scripts }}'));
    }
}
