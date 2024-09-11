<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Shopify\Support\RichTextConverter;
use StatamicRadPack\Shopify\Tests\TestCase;

class MetafieldsTest extends TestCase
{
    #[Test]
    public function parses_metafields_correctly()
    {
        $fields = app(config('shopify.metafields_parser'))->execute([
            [
                'id' => 1069228992,
                'namespace' => 'my_fields',
                'key' => 'sponsor',
                'value' => 'Shopify',
                'description' => null,
                'owner_id' => 382285388,
                'created_at' => '2023-07-11T18:10:28-04:00',
                'updated_at' => '2023-07-11T18:10:28-04:00',
                'owner_resource' => 'blog',
                'type' => 'single_line_text_field',
                'admin_graphql_api_id' => 'gid://shopify/Metafield/1069228992',
            ],
        ], 'product');

        $this->assertSame(['sponsor' => 'Shopify'], $fields);
    }

    #[Test]
    public function converts_rich_text_to_html()
    {
        $richText = '{"type":"root","children":[{"type":"paragraph","children":[{"type":"text","value":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]},{"type":"paragraph","children":[{"type":"text","value":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]},{"type":"paragraph","children":[{"type":"text","value":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]},{"type":"paragraph","children":[{"type":"text","value":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."},{"type":"link","url":"https://some.url","title":null,"target":null,"children":[{"type":"text","value":"I am a link"}]},{"type":"text","value":", but i am not."}]},{"type":"paragraph","children":[{"type":"text","value":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]}]}';

        $html = (new RichTextConverter)->convert($richText);

        $this->assertSame($html, '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.<a href="https://some.url" title="" target="">I am a link</a>, but i am not.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>');
    }

    #[Test]
    public function converts_rich_text_to_bard()
    {
        $richText = '{"type":"root","children":[{"type":"paragraph","children":[{"type":"text","value":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]},{"type":"paragraph","children":[{"type":"text","value":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]},{"type":"paragraph","children":[{"type":"text","value":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]},{"type":"paragraph","children":[{"type":"text","value":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."},{"type":"link","url":"https://some.url","title":null,"target":null,"children":[{"type":"text","value":"I am a link"}]},{"type":"text","value":", but i am not."}]},{"type":"paragraph","children":[{"type":"text","value":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]}]}';

        $bard = (new RichTextConverter)->convert($richText, true);

        $this->assertSame(json_encode($bard), '[{"type":"paragraph","attrs":{"textAlign":"left"},"content":[{"type":"text","text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]},{"type":"paragraph","attrs":{"textAlign":"left"},"content":[{"type":"text","text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]},{"type":"paragraph","attrs":{"textAlign":"left"},"content":[{"type":"text","text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]},{"type":"paragraph","attrs":{"textAlign":"left"},"content":[{"type":"text","text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."},{"type":"text","text":"I am a link","marks":[{"type":"link","attrs":{"href":"https:\/\/some.url"}}]},{"type":"text","text":", but i am not."}]},{"type":"paragraph","attrs":{"textAlign":"left"},"content":[{"type":"text","text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."}]}]');
    }
}
