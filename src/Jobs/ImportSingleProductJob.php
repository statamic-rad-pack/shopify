<?php

namespace Jackabox\Shopify\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use PHPShopify\ShopifySDK;
use Statamic\Facades\Asset;
use Statamic\Facades\Entry;
use Statamic\Facades\Path;

class ImportSingleProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int */
    public $slug;

    /** @var array */
    public $data;

    /**
     * ImportSingleProductJob constructor.
     *
     * @param array $data
     * @param string|null $handle
     */
    public function __construct(array $data, string $handle = null)
    {
        $this->data = $data;
        $this->slug = $handle ? $handle : $data['handle'];
    }

    /**
     *
     */
    public function handle()
    {
        $entry = Entry::query()
            ->where('collection', 'products')
            ->where('slug', $this->slug)
            ->first();

        $formatTags = [];
        $tags = $this->data['tags'] ? explode(', ', $this->data['tags']) : null;

        if ($tags) {
            foreach ($tags as $tag) {
                $formatTags = $this->formatStrings($tag);
            }
        }

        $data = [
            'product_id' => $this->data['id'],
            'published_at' => Carbon::parse($this->data['published_at'])->format('Y-m-d H:i:s'),
            'title' => (!$entry || config('shopify.overwrite.title')) ? $this->data['title'] : $entry->title,
            'content' => (!$entry || config('shopify.overwrite.content')) ? $this->data['body_html'] : $entry->content,
            'vendor' => (!$entry || config('shopify.overwrite.vendor')) ? $this->formatStrings($this->data['vendor']) : $entry->vendor,
            'type' => (!$entry || config('shopify.overwrite.type')) ? $this->formatStrings($this->data['product_type']) : $entry->type,
            'tags' => (!$entry || config('shopify.overwrite.tags')) ? $formatTags : $entry->tags,
        ];

        if (!$entry) {
            $entry = Entry::make()
                ->collection('products')
                ->slug($this->data['handle']);
        }

        // Import Variant
        $this->importVariants($this->data['variants'], $this->data['handle']);

        // Import Images
        $asset = $this->importImages($this->data['image']);
        $data['featured_image'] = $asset->path();

        // TODO: Wrap this in a conditional if they want it.
        foreach ($this->data['images'] as $image) {
            $asset = $this->importImages($image);
            $data['gallery'][] = $asset->path();
        }

        $entry->data($data)->save();
    }

    /**
     * @param array $variants
     * @param string $product_slug
     */
    private function importVariants(array $variants, string $product_slug)
    {
        foreach ($variants as $variant) {
            $entry = Entry::query()
                ->where('collection', 'variants')
                ->where('slug', $variant['id'])
                ->first();

            if (!$entry) {
                $entry = Entry::make()
                    ->collection('variants')
                    ->slug($variant['id']);
            }

            $entry->data([
                'variant_id' => $variant['id'],
                'product_slug' => $product_slug,
                'title' => $variant['title'],
                'inventory_quantity' => $variant['inventory_quantity'],
                'price' => $variant['price'],
                'sku' => $variant['sku'],
                'grams' => $variant['grams'],
                'requires_shipping' => $variant['requires_shipping'],
                'option1' => $variant['option1'],
                'option2' => $variant['option2'],
                'option3' => $variant['option3'],
                'storefront_id' => base64_encode($variant['admin_graphql_api_id']),
            ])->save();
        }
    }

    /**
     * TODO: Look into this one.
     * Not implemented due to issues with stack and saving images.
     * Currently images are all stored on the defalt product as a gallery.
     *
     * @param $variant
     */
    private function importImagesToVariant($variant)
    {
        $images = collect($this->data['images']);

        $variant_image = $images->filter(function ($item) use ($variant) {
            return in_array($variant['id'], $item['variant_ids']);
        })->first();

        if ($variant_image) {
            $asset = $this->importImages($variant_image);
        }
    }

    /**
     * TODO: check the container is the one we want from the config
     *
     * @param array $image
     * @return mixed
     */
    private function importImages(array $image)
    {
        $url = $this->cleanImageURL($image['src']);
        $name = $this->getImageNameFromUrl($url);
        $file = $this->uploadFakeFileFromUrl($name, $url);

        // Check if it exists first - no point double importing.
        $asset = Asset::query()
            ->where('container', 'shopify')
            ->where('path', 'Shopify/' . $name)
            ->first();

        if ($asset) {
            return $asset->hydrate();
        }

        // If it doesn't exists, let's make it exist.
        $asset = Asset::make()
            ->container('shopify')
            ->path($this->getPath($file));

        $asset->upload($file)->save();
        $asset->hydrate();

        $this->cleanupFakeFile($name);

        return $asset;
    }

    /**
     * Clean up any query params ont he end of the URL.
     *
     * @param string $url
     * @return string
     */
    private function cleanImageURL(string $url): string
    {
        return strtok($url, '?');
    }

    /**
     * Grab the image name from the file.
     *
     * @param string $url
     * @return string
     */
    private function getImageNameFromUrl(string $url): string
    {
        return substr($url, strrpos($url, '/') + 1);
    }

    /**
     * Make a fake file so Statamic can interpert the data we need.
     *
     * @param string $name
     * @param string $url
     * @return UploadedFile
     */
    public function uploadFakeFileFromUrl(string $name, string $url): UploadedFile
    {
        Storage::disk('local')->put($name, file_get_contents($url));

        return new UploadedFile(realpath(storage_path("app/$name")), $name);
    }

    /**
     * Remove the fake file as we don't need it lingering around.
     *
     * @param string $name
     */
    private function cleanupFakeFile(string $name): void
    {
        Storage::disk('local')->delete($name);
    }

    /**
     * TODO: let's make asset container variable.
     *
     * Get the path to upload to based on name/params.
     *
     * @param UploadedFile $file
     * @return String
     */
    private function getPath(UploadedFile $file): String
    {
        return Path::assemble('Shopify/', $file->getClientOriginalName());
    }

    /**
     * String formatter
     */
    private function formatStrings($string): string
    {
        return ucwords(str_replace('-', ' ', $string));
    }
}
