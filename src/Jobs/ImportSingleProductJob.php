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
use StatamicRadPack\Shopify\Traits\SavesImagesAndMetafields;
use Statamic\Support\Str;

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
        $tags = $this->importTaxonomy($this->data['tags'], config('shopify.taxonomies.tags'));
        $vendors = $this->importTaxonomy($this->data['vendor'], config('shopify.taxonomies.vendor'));
        $type = $this->importTaxonomy($this->data['product_type'], config('shopify.taxonomies.type'));

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
            'options' => $options,
        ];

        if (! $entry || config('shopify.overwrite.vendor')) {
            $data[config('shopify.taxonomies.vendor')] = $vendors;
        }

        if (! $entry || config('shopify.overwrite.type')) {
            $data[config('shopify.taxonomies.type')] = $type;
        }

        if (! $entry || config('shopify.overwrite.tags')) {
            $data[config('shopify.taxonomies.tags')] = $tags;
        }

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

        $entry->merge($data);

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

    private function importTaxonomy(string $tags, string $taxonomyHandle)
    {
        if (! $tags){
            return null;
        }

        $tags = explode(', ', $tags);

        // 'Tag foo, Tag bar' => ['tag-foo' => 'Tag foo', 'tag-bar' => 'Tag bar']
        $tags = collect($tags)
            ->mapWithKeys(fn ($tagTitle) => [Str::slug($tagTitle) => $tagTitle])
            ->each(function($tagTitle, $tagSlug) use ($taxonomyHandle) {
                $term = Term::query()
                    ->where('taxonomy', $taxonomyHandle)
                    ->where('slug', $tagSlug)
                    ->first();

                if (! $term) {
                    $term = Term::make()
                        ->taxonomy($taxonomyHandle)
                        ->slug($tagSlug);

                    $term->data([
                        'title' => $tagTitle,
                    ]);

                    $term->save();
                }
            });

        return $tags->keys()->toArray();
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

            $data = [
                'variant_id' => $variant['id'],
                'product_slug' => $product_slug,
                'title' => $variant['title'] === 'Default Title' ? 'Default' : $variant['title'],
                'inventory_quantity' => $variant['inventory_quantity'],
                'inventory_policy' => $variant['inventory_policy'],
                'inventory_management' => $variant['inventory_management'],
                'price' => $variant['price'],
                'compare_at_price' => $variant['compare_at_price'],
                'sku' => $variant['sku'],
                'grams' => $variant['grams'],
                'requires_shipping' => $variant['requires_shipping'],
                'option1' => $variant['option1'],
                'option2' => $variant['option2'],
                'option3' => $variant['option3'],
                'storefront_id' => base64_encode($variant['admin_graphql_api_id'])
            ];

            if ($variant['image_id']) {
                foreach (($this->data['images'] ?? []) as $image) {
                    if ($image['id'] == $variant['image_id']) {
                        $asset = $this->importImages($image);
                        $data['image'] = $asset->path();
                    }
                }
            }

            $entry->merge($data);

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
    private function removeOldVariants(array $variants, string $productSlug)
    {
        $allVariants = Entry::query()
            ->where('collection', 'variants')
            ->where('product_slug', $productSlug)
            ->get()
            ->each(function ($variant) use ($variants) {
                $item = array_search($variant->slug(), array_column($variants, 'id'));

                if ($item === false) {
                    $variant->delete();
                }
            });
    }
}
