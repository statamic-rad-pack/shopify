<?php

namespace StatamicRadPack\Shopify\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Asset;
use Statamic\Facades\Path;

trait SavesImagesAndMetafields
{
    /**
     * @return mixed
     */
    private function importImages(array $image)
    {
        if (! $url = $this->cleanImageURL($image['url'])) {
            return;
        }

        $name = $this->getImageNameFromUrl($url);

        // Check if it exists first - no point double importing.
        $asset = Asset::query()
            ->where('container', config('shopify.asset.container'))
            ->where('path', $this->getAssetPath($name))
            ->first();

        $altText = $image['altText'] ?? null;

        if ($asset) {
            if ($altText && $asset->get('alt') !== $altText) {
                $asset->set('alt', $altText)->save();
            }

            return $asset;
        }

        try {
            $file = $this->uploadFakeFileFromUrl($name, $url);
        } catch (\Throwable $e) {
            Log::warning('Shopify: could not download image from '.$url.': '.$e->getMessage());

            return null;
        }

        // If it doesn't exists, let's make it exist.
        $asset = Asset::make()
            ->container(config('shopify.asset.container'))
            ->path($this->getPath($file));

        $asset = $asset->upload($file);

        if ($altText) {
            $asset->set('alt', $altText);
        }

        $asset->save();

        $this->cleanupFakeFile($name);

        return $asset;
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

        Storage::disk('local')->put($name, $request->get($url)->throw()->body());

        return new UploadedFile(Storage::disk('local')->path($name), $name);
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
    private function cleanupFakeFile(string $name): void
    {
        Storage::disk('local')->delete($name);
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
