<?php

namespace StatamicRadPack\Shopify\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades\Asset;
use Statamic\Facades\Path;
use Statamic\Support\Str;
use StatamicRadPack\Shopify\Support\DownloadedReplacementFile;

trait SavesImagesAndMetafields
{
    /**
     * Images re-downloaded or replaced because they changed in Shopify.
     */
    protected int $updatedImages = 0;

    /**
     * Import an image, or update the asset already imported for it.
     *
     * A new version of an image with the same file name is re-uploaded over the
     * existing asset (Statamic's "Reupload"). An image Shopify now serves under
     * a new file name replaces the asset previously imported for the same media
     * ID (Statamic's "Replace"), which also updates references to it elsewhere.
     *
     * @param  string|null  $mediaId  the Shopify media / image ID
     * @param  array  $previousPaths  asset paths the entry used before this import
     * @return mixed
     */
    private function importImages(array $image, ?string $mediaId = null, array $previousPaths = [])
    {
        if (! $url = $this->cleanImageURL($image['url'])) {
            return;
        }

        $name = $this->getImageNameFromUrl($url);
        $version = $this->getImageVersion($image['url']);
        $altText = $image['altText'] ?? null;

        // Check if it exists first - no point double importing.
        $asset = Asset::query()
            ->where('container', config('shopify.asset.container'))
            ->where('path', $this->getAssetPath($name))
            ->first();

        if ($asset) {
            // assets imported before versions were recorded just record it, rather than downloading again
            $stored = $asset->get('shopify_image_version');

            if ($version && $stored && $stored !== $version && ! $this->reuploadImage($asset, $name, $url)) {
                $version = $stored; // try again on the next import
            }

            $this->saveShopifyImageData($asset, $mediaId, $version, $altText);

            return $asset;
        }

        try {
            $file = $this->uploadFakeFileFromUrl($name, $url);
        } catch (\Throwable $e) {
            Log::warning('Shopify: could not download image from '.$url.': '.$e->getMessage());

            return null;
        }

        $previous = $mediaId ? $this->findPreviousImage($mediaId, $previousPaths) : null;

        // If it doesn't exists, let's make it exist.
        $asset = Asset::make()
            ->container(config('shopify.asset.container'))
            ->path($this->getPath($file));

        $asset = $asset->upload($file);

        $this->cleanupFakeFile($file);

        // keep what editors added to the old asset (captions, focus point...)
        if ($previous) {
            $asset->merge($previous->data()->all());
        }

        $this->saveShopifyImageData($asset, $mediaId, $version, $altText, force: (bool) $previous);

        if ($previous) {
            $asset->replace($previous, (bool) config('shopify.asset.delete_replaced', false));
            $this->updatedImages++;
        }

        return $asset;
    }

    /**
     * Download a new version of an image over its existing asset.
     */
    private function reuploadImage(AssetContract $asset, string $name, string $url): bool
    {
        try {
            $file = $this->uploadFakeFileFromUrl($name, $url);
        } catch (\Throwable $e) {
            Log::warning('Shopify: could not download the new version of '.$url.': '.$e->getMessage());

            return false;
        }

        try {
            $asset->reupload(new DownloadedReplacementFile($this->localPath($file)));
            $this->updatedImages++;

            return true;
        } catch (\Throwable $e) {
            Log::warning('Shopify: could not replace '.$asset->path().' with the new version of '.$url.': '.$e->getMessage());

            return false;
        } finally {
            $this->cleanupFakeFile($file);
        }
    }

    /**
     * The asset previously imported for a Shopify image, among the paths an entry used.
     */
    private function findPreviousImage(string $mediaId, array $paths): ?AssetContract
    {
        foreach (array_unique(array_filter($paths, 'is_string')) as $path) {
            $asset = Asset::find(config('shopify.asset.container').'::'.$path);

            if ($asset && $asset->get('shopify_media_id') === $mediaId) {
                return $asset;
            }
        }

        return null;
    }

    /**
     * Record where an image came from (and its alt text), saving only when something changed.
     */
    private function saveShopifyImageData(AssetContract $asset, ?string $mediaId, ?string $version, ?string $altText, bool $force = false): void
    {
        $changes = array_filter([
            'alt' => $altText,
            'shopify_media_id' => $mediaId,
            'shopify_image_version' => $version,
        ], fn ($value, $key) => $value !== null && $value !== '' && $asset->get($key) !== $value, ARRAY_FILTER_USE_BOTH);

        foreach ($changes as $key => $value) {
            $asset->set($key, $value);
        }

        if ($changes || $force) {
            $asset->save();
        }
    }

    /**
     * Shopify's CDN URLs carry the file's version, e.g. ?v=1700000000, which changes when the file does.
     */
    private function getImageVersion(string $url): ?string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return isset($query['v']) && is_string($query['v']) && $query['v'] !== '' ? $query['v'] : null;
    }

    /**
     * Clean up any query params ont he end of the URL.
     */
    private function cleanImageURL(string $url): string
    {
        return strtok($url, '?');
    }

    /**
     * Grab the image name from the file.
     */
    private function getImageNameFromUrl(string $url): string
    {
        return substr($url, strrpos($url, '/') + 1);
    }

    /**
     * Make a fake file so Statamic can interpret the data we need.
     */
    public function uploadFakeFileFromUrl(string $name, string $url): UploadedFile
    {
        $request = Http::timeout(30);

        // Shopify's CDN does content negotiation, so without an Accept header it
        // may return a format that doesn't match the extension in the URL (and
        // therefore not the filename we write to disk). Ask for what the
        // extension promises.
        if ($accept = $this->acceptHeaderForExtension($url)) {
            $request = $request->withHeaders(['Accept' => $accept]);
        }

        // unique folder per download, so concurrent workers fetching an image with the
        // same file name can't overwrite or delete each other's temp file
        $path = 'shopify-tmp/'.Str::uuid().'/'.$name;

        Storage::disk('local')->put($path, $request->get($url)->throw()->body());

        return new UploadedFile(Storage::disk('local')->path($path), $name);
    }

    /**
     * Map the file extension in a URL to the image mime type to request.
     */
    private function acceptHeaderForExtension(string $url): ?string
    {
        return match (strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION))) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'avif' => 'image/avif',
            default => null,
        };
    }

    /**
     * Remove the fake file as we don't need it lingering around.
     */
    private function cleanupFakeFile(UploadedFile $file): void
    {
        $folder = dirname($this->localPath($file));

        // only ever remove the per-download folder created above
        if (str_starts_with($folder, 'shopify-tmp/')) {
            Storage::disk('local')->deleteDirectory($folder);
        }
    }

    /**
     * A downloaded file's path on the local disk.
     */
    private function localPath(UploadedFile $file): string
    {
        return Str::after($file->getPathname(), Storage::disk('local')->path(''));
    }

    /**
     * TODO: let's make asset container variable.
     *
     * Get the path to upload to based on name/params.
     */
    private function getPath(UploadedFile $file): string
    {
        return $this->getAssetPath($file->getClientOriginalName());
    }

    /**
     * Build the container-relative asset path for a file name. Used by both the
     * read (duplicate check) and write (upload) paths so the two cannot drift.
     */
    private function getAssetPath(string $name): string
    {
        return ltrim(Path::assemble(config('shopify.asset.path'), $name), '/');
    }

    /**
     * Parse metafields and hand off to our metafield handler
     */
    private function parseMetafields(array $fields, string $context): array
    {
        return app(config('shopify.metafields_parser'))->execute($fields, $context) ?? [];
    }
}
