<?php

namespace StatamicRadPack\Shopify\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Shopify\Clients\Graphql;
use Shopify\Clients\Rest;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use StatamicRadPack\Shopify\Traits\SavesImagesAndMetafields;

class ImportSingleProductJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SavesImagesAndMetafields;
    use SerializesModels;

    /** @var array */
    public $data;

    /** @var array */
    public $orderData;

    /**
     * ImportSingleProductJob constructor.
     */
    public function __construct(array $data, array $orderData = [])
    {
        $this->data = $data;
        $this->orderData = $orderData;
    }

    public function handle()
    {
        $entry = Entry::query()
            ->where('collection', 'products')
            ->where('site', Site::default()->handle())
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

        $published = $this->data['status'] === 'active' ? true : false;

        $data = [
            'product_id' => $this->data['id'],
            'published_at' => $this->data['status'] === 'active' ? Carbon::parse($this->data['published_at'])->format('Y-m-d H:i:s') : null,
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
                ->locale(Site::default()->handle())
                ->slug($this->data['handle']);
        }

        // Import Variant
        $this->importVariants($this->data['variants'], $this->data['handle']);

        // Import Images
        if ($this->data['image']) {
            if ($asset = $this->importImages($this->data['image'])) {
                $data['featured_image'] = $asset->path();
            }
        }

        if ($this->data['images']) {
            foreach ($this->data['images'] as $image) {
                if ($asset = $this->importImages($image)) {
                    $data['gallery'][] = $asset->path();
                }
            }
        }

        if ($this->orderData && ($quantities = Arr::get($this->orderData, 'quantity'))) {
            $qty = 0;
            foreach ($quantities as $sku => $q) {
                $qty += (int) $q;
            }

            $data = $this->updatePurchaseHistory($data, $qty);
        }

        $entry->merge($data);

        // this is to make testing easier
        // means we can just test individual parts of the job
        try {
            $query = <<<QUERY
              query {
                product(id: "gid://shopify/Product/{$this->data['id']}") {
                  collections(first: 100) {
                    edges {
                       node {
                        id
                        handle
                      }
                    }
                  }
                  metafields(first: 100) {
                    edges {
                       node {
                        id
                        jsonValue
                        key
                        value
                      }
                    }
                  }
                  resourcePublications(onlyPublished: false, first:100) {
                    edges {
                      node {
                        isPublished
                        publication {
                            id
                        }
                        publishDate
                      }
                    }
                  }
                }
              }
            QUERY;

            $response = app(Graphql::class)->query(['query' => $query]);

            // collections
            try {
                $collections = collect(Arr::get($response->getDecodedBody(), 'data.product.collections.edges', []))
                    ->map(function ($collection) {
                        if (! $node = $collection['node'] ?? []) {
                            return [];
                        }

                        $term = Term::query()
                            ->where('slug', $node['handle'])
                            ->where('taxonomy', config('shopify.taxonomies.collections'))
                            ->first();

                        if (! $term) {
                            return;
                        }

                        return $node['handle'];
                    })
                    ->filter()
                    ->all();

                $entry->set(config('shopify.taxonomies.collections'), $collections)->save();
            } catch (\Throwable $e) {
                Log::error('Could not retrieve collections for product '.$this->data['id']);
                Log::error($e->getMessage());
            }

            // meta fields
            try {
                $metafields = collect(Arr::get($response->getDecodedBody(), 'data.product.metafields.edges', []))->map(fn ($metafield) => $metafield['node'] ?? [])->filter()->all();

                if ($metafields) {
                    $metafields = $this->parseMetafields($metafields, 'product');

                    if ($metafields) {
                        $entry->merge($metafields);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Could not retrieve metafields for product '.$this->data['id']);
                Log::error($e->getMessage());
            }

            // publication state
            try {
                $publicationStatus = collect(Arr::get($response->getDecodedBody(), 'data.product.resourcePublications.edges', []))
                    ->where('node.publication.name', 'Online Store')
                    ->map(function ($channel) {
                        if (! $node = $channel['node'] ?? []) {
                            return [];
                        }

                        return $node;
                    })
                    ->filter()
                    ->first();

                if ($publicationStatus) {
                    $published = $publicationStatus['isPublished'] ?? false;

                    if ($entry->collection()->dated() && $publicationStatus['publishDate']) {
                        $publishDate = Carbon::parse($publicationStatus['publishDate']);

                        $entry->date($publishDate);

                        if (! $published && $publishDate->gt(now())) {
                            $published = true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Could not manage publications status for product '.$this->data['id']);
                Log::error($e->getMessage());
            }

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }

        $entry->published($published);
        $entry->save();

        // if we are multisite, get translations
        if (Site::hasMultiple()) {
            $collectionSites = $entry->collection()->sites();

            Site::all()->each(function ($site) use ($collectionSites, $entry) {
                if (Site::default()->handle() == $site->handle()) {
                    return;
                }

                if (! $collectionSites->contains($site->handle())) {
                    return;
                }

                $query = <<<QUERY
                  query {
                    translatableResource(resourceId: "gid://shopify/Product/{$this->data['id']}") {
                      resourceId
                      translations(locale: "{$site->locale()}") {
                        key
                        value
                      }
                    }
                  }
                QUERY;

                $response = app(Graphql::class)->query(['query' => $query]);

                $translations = Arr::get($response->getDecodedBody(), 'data.translatableResource.translations', []);

                if ($translations) {
                    $localizedEntry = $entry->in($site->handle());

                    if (! $localizedEntry) {
                        $localizedEntry = $entry->makeLocalization($site);
                    }

                    $data = collect($translations)->mapWithKeys(fn ($row) => [$row['key'] == 'body_html' ? 'content' : $row['key'] => $row['value']]);

                    $localizedEntry->merge($data)->save();
                }
            });
        }
    }

    private function importTaxonomy(string $tags, string $taxonomyHandle)
    {
        if (! $tags) {
            return null;
        }

        $tags = explode(', ', $tags);

        // 'Tag foo, Tag bar' => ['tag-foo' => 'Tag foo', 'tag-bar' => 'Tag bar']
        $tags = collect($tags)
            ->mapWithKeys(fn ($tagTitle) => [Str::slug($tagTitle) => $tagTitle])
            ->each(function ($tagTitle, $tagSlug) use ($taxonomyHandle) {
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
                ->where('site', Site::default()->handle())
                ->first();

            if (! $entry) {
                $entry = Entry::make()
                    ->collection('variants')
                    ->locale(Site::default()->handle())
                    ->slug($variant['id']);
            }

            $data = [
                'variant_id' => $variant['id'],
                'product_slug' => $product_slug,
                'title' => $variant['title'] === 'Default Title' ? 'Default' : $variant['title'],
                'inventory_quantity' => $variant['inventory_quantity'] ?? null,
                'inventory_policy' => $variant['inventory_policy'] ?? null,
                'inventory_management' => $variant['inventory_management'] ?? null,
                'price' => $variant['price'],
                'compare_at_price' => $variant['compare_at_price'],
                'sku' => $variant['sku'],
                'grams' => $variant['grams'] ?? null,
                'requires_shipping' => $variant['requires_shipping'] ?? null,
                'option1' => $variant['option1'],
                'option2' => $variant['option2'] ?? '',
                'option3' => $variant['option3'] ?? '',
                'storefront_id' => base64_encode($variant['admin_graphql_api_id']),
            ];

            if ($variant['image_id']) {
                foreach (($this->data['images'] ?? []) as $image) {
                    if ($image['id'] == $variant['image_id']) {
                        if ($asset = $this->importImages($image)) {
                            $data['image'] = $asset->path();
                        }
                    }
                }
            }

            if ($this->orderData && ($qty = Arr::get($this->orderData, 'quantity.'.$variant['sku']))) {
                $data = $this->updatePurchaseHistory($data, (int) $qty);
            }

            $entry->merge($data);

            try {
                $response = app(Rest::class)->get(path: 'metafields', query: ['metafield' => ['owner_id' => $variant['id'], 'owner_resource' => 'variants']]);

                if ($response->getStatusCode() == 200) {
                    $metafields = Arr::get($response->getDecodedBody(), 'metafields', []);

                    if ($metafields) {
                        $metafields = $this->parseMetafields($metafields, 'product-variant');

                        if ($metafields) {
                            $entry->merge($metafields);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Could not retrieve metafields for variant '.$this->data['id']);
            }

            $entry->save();

            // if we are multisite, get translations
            if (Site::hasMultiple()) {
                $collectionSites = $entry->collection()->sites();

                Site::all()->each(function ($site) use ($collectionSites, $entry, $variant) {
                    if (Site::default()->handle() == $site->handle()) {
                        return;
                    }

                    if (! $collectionSites->contains($site->handle())) {
                        return;
                    }

                    $query = <<<QUERY
                      query {
                        translatableResource(resourceId: "gid://shopify/ProductVariant/{$variant['id']}") {
                          resourceId
                          translations(locale: "{$site->locale()}") {
                            key
                            value
                          }
                        }
                      }
                    QUERY;

                    $response = app(Graphql::class)->query(['query' => $query]);

                    $translations = Arr::get($response->getDecodedBody(), 'data.translatableResource.translations', []);

                    if ($translations) {
                        $localizedEntry = $entry->in($site->handle());

                        if (! $localizedEntry) {
                            $localizedEntry = $entry->makeLocalization($site);
                        }

                        $data = collect($translations)->mapWithKeys(fn ($row) => [$row['key'] => $row['value']]);

                        $localizedEntry->merge($data)->save();
                    }
                });
            }
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

    /**
     * Update the purchase history for this item
     */
    private function updatePurchaseHistory(array $data, int $qty): array
    {
        $data['last_purchased'] = $this->orderData['date']->format('Y-m-d H:i:s');

        $orderYearKey = 'total_purchased.'.$this->orderData['date']->format('Y').'.total';
        $orderMonthKey = 'total_purchased.'.$this->orderData['date']->format('Y').'.'.$this->orderData['date']->format('m');

        Arr::set($data, 'total_purchased.lifetime', Arr::get($data, 'total_purchased.lifetime', 0) + $qty);
        Arr::set($data, $orderYearKey, Arr::get($data, $orderYearKey, 0) + $qty);
        Arr::set($data, $orderMonthKey, Arr::get($data, $orderMonthKey, 0) + $qty);

        return $data;
    }
}
