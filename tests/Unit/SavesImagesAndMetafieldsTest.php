<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use StatamicRadPack\Shopify\Tests\TestCase;
use StatamicRadPack\Shopify\Traits\SavesImagesAndMetafields;

class SavesImagesAndMetafieldsTest extends TestCase
{
    use SavesImagesAndMetafields;

    #[Test]
    #[DataProvider('extensionAcceptHeaderProvider')]
    public function it_sends_an_accept_header_matching_the_url_extension(string $file, ?string $expected)
    {
        Http::fake([
            'cdn.shopify.com/*' => Http::response('fake-image-bytes'),
        ]);

        $this->uploadFakeFileFromUrl($file, 'https://cdn.shopify.com/s/files/1/0001/0001/products/'.$file);

        Http::assertSent(function (Request $request) use ($expected) {
            return $request->header('Accept') === ($expected ? [$expected] : []);
        });
    }

    public static function extensionAcceptHeaderProvider(): array
    {
        return [
            'webp' => ['schal1.webp', 'image/webp'],
            'png' => ['in-der-dorfkneipe.png', 'image/png'],
            'jpg' => ['socken.jpg', 'image/jpeg'],
            'jpeg' => ['socken.jpeg', 'image/jpeg'],
            'gif' => ['banner.gif', 'image/gif'],
            'unknown extension' => ['weird.xyz', null],
        ];
    }
}
