<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PHPShopify\ShopifySDK;
use Statamic\Facades\Term;
use Statamic\Shopify\Traits\SavesImagesAndMetafields;

class ImportCollectionsForProductJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use SavesImagesAndMetafields;

    public $product;

    public $collections;

    public function __construct($collections, $product)
    {
        $this->product = $product;
        $this->collections = $collections;
    }

    public function handle()
    {
        $product_collections = collect();

        foreach ($this->collections as $collection) {
            $term = Term::query()
                ->where('slug', $collection['handle'])
                ->where('taxonomy', config('shopify.taxonomies.collections'))
                ->first();

            if (! $term) {
                $term = Term::make()
                    ->taxonomy(config('shopify.taxonomies.collections'))
                    ->slug($collection['handle']);
            }

            $data = [
                'title' => $collection['title'],
                'collection_id' => $collection['id'],
                'content' => $collection['body_html'],
            ];

            // Import Images
            if ($collection['image']) {
                $asset = $this->importImages($collection['image']);
                $data['featured_image'] = $asset->path();
            }

            try {
                $collectionMetafields = (new ShopifySDK())->Collection($collection['id'])->Metafield()->get();
                $metafields = $this->parseMetafields($collectionMetafields, 'collection');

                if ($metafields) {
                    $data = array_merge($metafields, $data);
                }
            } catch (\Throwable $e) {
                Log::error('Could not retrieve metafields for product'.$this->data['id']);
            }

            $term->merge($data)->save();

            $product_collections->push($collection['handle']);
        }

        $this->product->set(config('shopify.taxonomies.collections'), $product_collections->toArray());
        $this->product->save();
    }

    /**
     * @return mixed
     */
    private function importImages(array $image)
    {
        $url = $this->cleanImageURL($image['src']);
        $name = $this->getImageNameFromUrl($url);
        $file = $this->uploadFakeFileFromUrl($name, $url);

        // Check if it exists first - no point double importing.
        $asset = Asset::query()
            ->where('container', config('shopify.asset.container'))
            ->where('path', config('shopify.asset.path').'/'.$name)
            ->first();

        if ($asset) {
            return $asset;
        }

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

        return new UploadedFile(realpath(storage_path("app/$name")), $name);
    }

    /**
     * Remove the fake file as we don't need it lingering around.
     */
    private function cleanupFakeFile(string $name): void
    {
        Storage::disk('local')->delete($name);
    }

    /**
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
