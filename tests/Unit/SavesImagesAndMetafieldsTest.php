<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\AssetReplaced;
use Statamic\Events\AssetReuploaded;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
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

    #[Test]
    #[DataProvider('assetPathProvider')]
    public function it_does_not_re_download_an_image_that_already_exists(string $assetPath)
    {
        config()->set('shopify.asset.path', $assetPath);

        Storage::fake('local');
        Storage::fake('assets');

        AssetContainer::make('shopify')->disk('assets')->save();

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+P+/HgAFhAJ/wlseKgAAAABJRU5ErkJggg==');

        Http::fake([
            'cdn.shopify.com/*' => Http::response($png),
        ]);

        $image = ['url' => 'https://cdn.shopify.com/s/files/1/0001/0001/products/schal1.png', 'altText' => null];

        $this->importImages($image);
        $this->importImages($image);

        $this->assertCount(1, Asset::query()->where('container', 'shopify')->get());
        Http::assertSentCount(1);
    }

    public static function assetPathProvider(): array
    {
        return [
            'empty path' => [''],
            'configured path' => ['shopify'],
        ];
    }

    private function fakeAssets(): void
    {
        Storage::fake('local');
        Storage::fake('assets');

        AssetContainer::make('shopify')->disk('assets')->save();
    }

    /**
     * Serve a distinct 1x1 PNG per request, so each download can be told apart.
     */
    private function fakeCdn(): void
    {
        $count = 0;

        Http::fake(['cdn.shopify.com/*' => function () use (&$count) {
            $count++;

            return Http::response($this->png($count));
        }]);
    }

    private function png(int $seed): string
    {
        $image = imagecreatetruecolor(1, 1);
        imagesetpixel($image, 0, 0, imagecolorallocate($image, $seed * 40 % 256, 0, 0));
        ob_start();
        imagepng($image);

        return ob_get_clean();
    }

    private function shirt(string $version, string $name = 'shirt.png'): array
    {
        return ['url' => "https://cdn.shopify.com/s/files/1/products/{$name}?v={$version}", 'altText' => 'A shirt'];
    }

    #[Test]
    public function records_the_shopify_media_id_and_version_on_the_asset()
    {
        $this->fakeAssets();
        $this->fakeCdn();

        $asset = $this->importImages($this->shirt('100'), 'gid://shopify/MediaImage/1');

        $this->assertSame('gid://shopify/MediaImage/1', $asset->get('shopify_media_id'));
        $this->assertSame('100', $asset->get('shopify_image_version'));
        $this->assertSame('A shirt', $asset->get('alt'));

        $this->importImages($this->shirt('100'), 'gid://shopify/MediaImage/1');

        Http::assertSentCount(1);
        $this->assertSame(0, $this->updatedImages);
    }

    #[Test]
    public function reuploads_a_new_version_of_an_image_over_the_same_asset()
    {
        $this->fakeAssets();
        $this->fakeCdn();

        $asset = $this->importImages($this->shirt('100'), 'gid://shopify/MediaImage/1');
        $asset->set('focus', '20-30-1')->save();
        $original = Storage::disk('assets')->get('shirt.png');

        Event::fake([AssetReuploaded::class]);

        $updated = $this->importImages($this->shirt('200'), 'gid://shopify/MediaImage/1');

        $this->assertSame($asset->id(), $updated->id());
        $this->assertNotSame($original, Storage::disk('assets')->get('shirt.png'));
        $this->assertSame('200', Asset::find('shopify::shirt.png')->get('shopify_image_version'));
        $this->assertSame('20-30-1', Asset::find('shopify::shirt.png')->get('focus'));
        $this->assertCount(1, Asset::query()->where('container', 'shopify')->get());
        $this->assertSame(1, $this->updatedImages);
        $this->assertSame([], Storage::disk('local')->allFiles(), 'The download should be cleaned up');
        Event::assertDispatched(AssetReuploaded::class);
    }

    #[Test]
    public function records_the_version_of_an_image_imported_before_versions_were_kept_without_downloading_it()
    {
        $this->fakeAssets();
        $this->fakeCdn();

        $this->importImages(['url' => 'https://cdn.shopify.com/s/files/1/products/shirt.png', 'altText' => null]);
        $this->importImages($this->shirt('300'), 'gid://shopify/MediaImage/1');

        Http::assertSentCount(1);
        $this->assertSame('300', Asset::find('shopify::shirt.png')->get('shopify_image_version'));
        $this->assertSame(0, $this->updatedImages);
    }

    #[Test]
    public function keeps_the_old_version_and_tries_again_when_the_new_one_cannot_be_downloaded()
    {
        $this->fakeAssets();
        Http::fake(['cdn.shopify.com/*' => Http::sequence()->push($this->png(1))->pushStatus(500)]);

        $this->importImages($this->shirt('100'), 'gid://shopify/MediaImage/1');
        $this->importImages($this->shirt('200'), 'gid://shopify/MediaImage/1');

        $this->assertSame('100', Asset::find('shopify::shirt.png')->get('shopify_image_version'));
        $this->assertSame(0, $this->updatedImages);
    }

    #[Test]
    public function replaces_the_previous_asset_when_shopify_serves_the_image_under_a_new_name()
    {
        $this->fakeAssets();
        $this->fakeCdn();

        $old = $this->importImages($this->shirt('100'), 'gid://shopify/MediaImage/1');
        $old->set('caption', 'Worn by our founder')->save();

        Event::fake([AssetReplaced::class]);

        $new = $this->importImages($this->shirt('200', 'shirt_v2.png'), 'gid://shopify/MediaImage/1', ['shirt.png']);

        $this->assertSame('shirt_v2.png', $new->path());
        $this->assertSame('Worn by our founder', $new->get('caption'));
        $this->assertSame('200', $new->get('shopify_image_version'));
        $this->assertSame(1, $this->updatedImages);
        Event::assertDispatched(AssetReplaced::class, fn ($event) => $event->originalAsset->path() === 'shirt.png' && $event->newAsset->path() === 'shirt_v2.png');

        // the old asset is kept unless configured otherwise
        $this->assertNotNull(Asset::find('shopify::shirt.png'));
    }

    #[Test]
    public function deletes_the_replaced_asset_when_configured()
    {
        config()->set('shopify.asset.delete_replaced', true);
        $this->fakeAssets();
        $this->fakeCdn();

        $this->importImages($this->shirt('100'), 'gid://shopify/MediaImage/1');
        $this->importImages($this->shirt('200', 'shirt_v2.png'), 'gid://shopify/MediaImage/1', ['shirt.png']);

        $this->assertNull(Asset::find('shopify::shirt.png'));
        $this->assertFalse(Storage::disk('assets')->exists('shirt.png'));
    }

    #[Test]
    public function only_replaces_an_asset_imported_for_the_same_shopify_image()
    {
        $this->fakeAssets();
        $this->fakeCdn();

        $this->importImages($this->shirt('100'), 'gid://shopify/MediaImage/1');

        Event::fake([AssetReplaced::class]);

        $this->importImages($this->shirt('100', 'hat.png'), 'gid://shopify/MediaImage/2', ['shirt.png', 'missing.png']);

        Event::assertNotDispatched(AssetReplaced::class);
        $this->assertSame(0, $this->updatedImages);
    }

    #[Test]
    public function downloads_each_image_to_its_own_temp_folder_and_cleans_it_up()
    {
        Http::fake(['cdn.shopify.com/*' => Http::response('fake-image-bytes')]);

        $first = $this->uploadFakeFileFromUrl('shirt.jpg', 'https://cdn.shopify.com/s/files/1/products/shirt.jpg');
        $second = $this->uploadFakeFileFromUrl('shirt.jpg', 'https://cdn.shopify.com/s/files/2/products/shirt.jpg');

        $this->assertNotSame($first->getPathname(), $second->getPathname());
        $this->assertSame('shirt.jpg', $first->getClientOriginalName());
        $this->assertFileExists($first->getPathname());

        $this->cleanupFakeFile($first);

        $this->assertFileDoesNotExist($first->getPathname());
        $this->assertFileExists($second->getPathname());

        $this->cleanupFakeFile($second);
    }
}
