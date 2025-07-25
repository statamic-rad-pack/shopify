<?php

namespace StatamicRadPack\Shopify\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Shopify\Clients\Graphql;
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

    public $data = [];

    public function __construct(public int $productId, public ?array $orderData = [])
    {
        if ($queue = config('shopify.queue')) {
            $this->onQueue($queue);
        }
    }

    public function handle()
    {
        $query = <<<QUERY
        {
          product(id: "gid://shopify/Product/{$this->productId}") {
            collections(first: 100) {
              edges {
                node {
                  id
                  handle
                }
              }
            }
            descriptionHtml
            handle
            id
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
            media(first: 20, sortKey: POSITION, query: "media_type:IMAGE") {
              edges {
                node {
                  id
                  ... on MediaImage {
                    id
                    image {
                      altText
                      url
                    }
                  }
                }
              }
            }
            options {
              name
              values
            }
            productType
            resourcePublications(onlyPublished: false, first: 100) {
              edges {
                node {
                  isPublished
                  publication {
                    id
                    name
                  }
                  publishDate
                }
              }
            }
            tags
            title
            variants(first: 100) {
              edges {
                node {
                  compareAtPrice
                  id
                  inventoryItem {
                    measurement {
                      weight {
                        unit
                        value
                      }
                    }
                    requiresShipping
                  }
                  inventoryPolicy
                  inventoryQuantity
                  media(first: 20) {
                    edges {
                      node {
                        id
                        ... on MediaImage {
                          id
                          image {
                            altText
                            url
                          }
                        }
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
                  price
                  selectedOptions {
                    name
                    optionValue {
                      id
                    }
                    value
                  }
                  sku
                  title
                }
              }
            }
            vendor
          }
        }
        QUERY;

        $response = app(Graphql::class)->query(['query' => $query]);

        if (! $this->data = Arr::get($response->getDecodedBody(), 'data.product', [])) {
            return;
        }

        $this->data['id'] = Str::afterLast($this->data['id'], '/');

        $entry = Entry::query()
            ->where('collection', config('shopify.collection_handle', 'products'))
            ->where('site', Site::default()->handle())
            ->where('product_id', $this->data['id'])
            ->first();

        // Clean up data whilst checking if product exists
        $tags = $this->importTaxonomy($this->data['tags'], config('shopify.taxonomies.tags'));
        $vendors = $this->importTaxonomy([$this->data['vendor']], config('shopify.taxonomies.vendor'));
        $type = $this->importTaxonomy([$this->data['productType']], config('shopify.taxonomies.type'));

        // Get option Names
        $options = [];
        foreach ($this->data['options'] as $index => $option) {
            if ($option['name'] != 'Title') {
                $options['option'.($index + 1)] = $option['name'];
            }
        }

        $data = [
            'product_id' => $this->data['id'],
            'title' => (! $entry || config('shopify.overwrite.title')) ? $this->data['title'] : $entry->title,
            'content' => (! $entry || config('shopify.overwrite.content')) ? $this->data['descriptionHtml'] : $entry->content,
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
                ->collection(config('shopify.collection_handle', 'products'))
                ->locale(Site::default()->handle())
                ->slug($this->data['handle']);
        }

        // Import Variant
        $this->importVariants($this->data['variants'], $this->data['handle']);

        // Import Images
        foreach (Arr::get($this->data, 'media.edges', []) as $index => $edge) {
            if (! $image = Arr::get($edge, 'node.image', [])) {
                continue;
            }

            if ($asset = $this->importImages($image)) {
                $data['gallery'][] = $asset->path();

                if ($index == 0) {
                    $data['featured_image'] = $asset->path();
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

        $published = false;

        // this is to make testing easier
        // means we can just test individual parts of the job
        try {

            // collections
            try {
                $collections = collect(Arr::get($this->data, 'collections.edges', []))
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
                $metafields = collect(Arr::get($this->data, 'metafields.edges', []))->map(fn ($metafield) => $metafield['node'] ?? [])->filter()->all();

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
                $publicationStatus = collect(Arr::get($this->data, 'resourcePublications.edges', []))
                    ->where('node.publication.name', config('shopify.sales_channel', 'Online Store'))
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

                        $entry->set('published_at', $publishDate->format('Y-m-d H:i:s'));
                        $entry->date($publishDate);

                        if (! $published && $publishDate->lt(now())) {
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
                      translations(locale: "{$site->lang()}") {
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

    private function importTaxonomy(array $tags, string $taxonomyHandle)
    {
        if (! $tags) {
            return null;
        }

        // 'Tag foo, Tag bar' => ['tag-foo' => 'Tag foo', 'tag-bar' => 'Tag bar']
        $tags = collect($tags)
            ->filter()
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

    private function importVariants(array $returnedVariants, string $product_slug)
    {
        $variants = [];
        foreach ($returnedVariants['edges'] as $variant) {
            $variant = $variant['node'];
            $variant['id'] = Str::afterLast($variant['id'], '/');

            $variants[] = $variant;
        }

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

            // see https://shopify.dev/docs/api/admin-graphql/latest/objects/ProductVariant
            $data = array_merge([
                'variant_id' => (int) $variant['id'],
                'product_slug' => $product_slug,
                'title' => $variant['title'] === 'Default Title' ? 'Default' : $variant['title'],
                'inventory_quantity' => $variant['inventoryQuantity'] ?? null,
                'inventory_policy' => $variant['inventoryPolicy'] ?? null,
                'inventory_management' => 'shopify', // @deprecated, left in for backwards JS compatibility
                'price' => $variant['price'],
                'compare_at_price' => $variant['compareAtPrice'],
                'sku' => $variant['sku'],
                'weight' => Arr::get($variant, 'inventoryItem.measurement.weight', null), // blueprint update: was grams, this has unit and value now
                'requires_shipping' => Arr::get($variant, 'inventoryItem.requiresShipping', null),
            ], collect($variant['selectedOptions'] ?? [])->mapWithKeys(fn ($opt, $index) => ['option'.($index + 1) => $opt['value']])->all());  // blueprint update: what if there are more than 3?

            foreach (Arr::get($variant, 'media.edges', []) as $index => $edge) {
                if (! $image = Arr::get($edge, 'node.image', [])) {
                    continue;
                }

                if ($asset = $this->importImages($image)) {
                    $data['gallery'][] = $asset->path();

                    if ($index == 0) {
                        $data['image'] = $asset->path();
                    }
                }
            }

            if ($this->orderData && ($qty = Arr::get($this->orderData, 'quantity.'.$variant['sku']))) {
                $data = $this->updatePurchaseHistory($data, (int) $qty);
            }

            $entry->merge($data);

            try {
                $metafields = collect(Arr::get($variant, 'metafields.edges', []))->map(fn ($metafield) => $metafield['node'] ?? [])->filter()->all();

                if ($metafields) {
                    $metafields = $this->parseMetafields($metafields, 'product-variant');

                    if ($metafields) {
                        $entry->merge($metafields);
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
                          translations(locale: "{$site->lang()}") {
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
        Entry::query()
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
