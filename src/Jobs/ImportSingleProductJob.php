<?php

namespace StatamicRadPack\Shopify\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PHPShopify\ShopifySDK;
use Statamic\Facades\Entry;
use Statamic\Shopify\Traits\SavesImagesAndMetafields;

class ImportSingleProductJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use SavesImagesAndMetafields;

    /** @var int */
    public $slug;

    /** @var array */
    public $data;

    /**
     * ImportSingleProductJob constructor.
     */
    public function __construct(array $data, string $handle = null)
    {
        $this->data = $data;
        $this->slug = $handle ? $handle : $data['handle'];
    }

    public function handle()
    {
        $entry = Entry::query()
            ->where('collection', 'products')
            ->where('product_id', $this->data['id'])
            ->first();

        // Clean up data whilst checking if product exists
        $tags = $this->cleanArrayData($this->data['tags']);
        $vendors = $this->cleanArrayData($this->data['vendor']);
        $type = $this->cleanArrayData($this->data['product_type']);

        // Get option Names
        $options = [];
        foreach ($this->data['options'] as $option) {
            if ($option['name'] != 'Title') {
                $options['option'.$option['position']] = $option['name'];
            }
        }

        $data = [
            'product_id' => $this->data['id'],
            'published' => $this->data['status'] === 'active' ? true : false,
            'published_at' => Carbon::parse($this->data['published_at'])->format('Y-m-d H:i:s'),
            'title' => (! $entry || config('shopify.overwrite.title')) ? $this->data['title'] : $entry->title,
            'content' => (! $entry || config('shopify.overwrite.content')) ? $this->data['body_html'] : $entry->content,
            config('shopify.taxonomies.vendor') => (! $entry || config('shopify.overwrite.vendor')) ? $vendors : $entry->vendor,
            config('shopify.taxonomies.type') => (! $entry || config('shopify.overwrite.type')) ? $type : $entry->product_type,
            config('shopify.taxonomies.tags') => (! $entry || config('shopify.overwrite.tags')) ? $tags : $entry->product_tags,
            'options' => $options,
        ];

        if (! $entry) {
            $entry = Entry::make()
                ->collection('products')
                ->slug($this->data['handle']);
        }

        // Import Variant
        $this->importVariants($this->data['variants'], $this->data['handle']);

        // Import Images
        if ($this->data['image']) {
            $asset = $this->importImages($this->data['image']);
            $data['featured_image'] = $asset->path();
        }

        if ($this->data['images']) {
            foreach ($this->data['images'] as $image) {
                $asset = $this->importImages($image);
                $data['gallery'][] = $asset->path();
            }
        }

        // Recursively loop over the products and set the data as needed.
        foreach ($data as $key => $prop) {
            $entry->set($key, $prop);
        }

        try {
            $productMetafields = (new ShopifySDK())->Product($this->data['id'])->Metafield()->get();
            $metafields = $this->parseMetafields($productMetafields, 'product');

            if ($metafields) {
                $entry->merge($metafields);
            }
        } catch (\Throwable $e) {
            Log::error('Could not retrieve metafields for product '.$this->data['id']);
        }

        $entry->save();

        // Get the collections
        FetchCollectionsForProductJob::dispatch($entry)->onQueue(config('shopify.queue'));
    }

    private function importVariants(array $variants, string $product_slug)
    {
        $this->removeOldVariants($variants, $product_slug);

        foreach ($variants as $variant) {
            $entry = Entry::query()
                ->where('collection', 'variants')
                ->where('slug', $variant['id'])
                ->first();

            if (! $entry) {
                $entry = Entry::make()
                    ->collection('variants')
                    ->slug($variant['id']);
            }

            $entry->set('variant_id', $variant['id']);
            $entry->set('product_slug', $product_slug);
            $entry->set('title', $variant['title'] === 'Default Title' ? 'Default' : $variant['title']);
            $entry->set('inventory_quantity', $variant['inventory_quantity']);
            $entry->set('inventory_policy', $variant['inventory_policy']);
            $entry->set('inventory_management', $variant['inventory_management']);
            $entry->set('price', $variant['price']);
            $entry->set('compare_at_price', $variant['compare_at_price']);
            $entry->set('sku', $variant['sku']);
            $entry->set('grams', $variant['grams']);
            $entry->set('requires_shipping', $variant['requires_shipping']);
            $entry->set('option1', $variant['option1']);
            $entry->set('option2', $variant['option2']);
            $entry->set('option3', $variant['option3']);
            $entry->set('storefront_id', base64_encode($variant['admin_graphql_api_id']));

            try {
                $variantMetafields = (new ShopifySDK())->ProductVariant($variant['id'])->Metafield()->get();
                $metafields = $this->parseMetafields($variantMetafields, 'product-variant');

                if ($metafields) {
                    $entry->merge($metafields);
                }
            } catch (\Throwable $e) {
                Log::error('Could not retrieve metafields for variant '.$this->data['id']);
            }

            $entry->save();
        }
    }

    /**
     * Remove old variants that are no longer used on a single product.
     */
    private function removeOldVariants(array $variants, string $product_slug)
    {
        $allVariants = Entry::query()
            ->where('collection', 'variants')
            ->where('product_slug', $product_slug)
            ->get();

        foreach ($allVariants as $variant) {
            $item = array_search($variant->slug(), array_column($variants, 'id'));

            if ($item === false) {
                $variant->delete();
            }
        }
    }

    /**
     * TODO: Look into this one.
     * Not implemented due to issues with stack and saving images.
     * Currently images are all stored on the defalt product as a gallery.
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
}
