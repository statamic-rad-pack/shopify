<?php

namespace StatamicRadPack\Shopify\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Asset;
use Statamic\Facades\Path;
use Statamic\Support\Str;

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
            ->where('path', config('shopify.asset.path').'/'.$name)
            ->first();

        if ($asset) {
            return $asset;
        }

        $file = $this->uploadFakeFileFromUrl($name, $url);

        // If it doesn't exists, let's make it exist.
        $asset = Asset::make()
            ->container(config('shopify.asset.container'))
            ->path($this->getPath($file));

        $asset->upload($file)->save();

        $this->cleanupFakeFile($name);

        return $asset;
    }

    private function cleanArrayData($data)
    {
        if (! $data) {
            return null;
        }

        $formattedItems = [];
        $items = explode(', ', $data);

        if ($items) {
            foreach ($items as $item) {
                $formattedItems[] = Str::slug($item);
            }
        }

        return $formattedItems;
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
        Storage::disk('local')->put($name, file_get_contents($url));

        return new UploadedFile(Storage::disk('local')->path($name), $name);
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
        return Path::assemble(config('shopify.asset.path').'/', $file->getClientOriginalName());
    }

    /**
     * Parse metafields and hand off to our metafield handler
     */
    private function parseMetafields(array $fields, string $context): array
    {
        return app(config('shopify.metafields_parser'))->execute($fields, $context) ?? [];
    }
}
