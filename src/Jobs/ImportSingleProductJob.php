<?php

namespace StatamicRadPack\Shopify\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\Hookable;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Events\ProductImportFailed;
use StatamicRadPack\Shopify\Support\ProductSummary;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Traits\SavesImagesAndMetafields;
use StatamicRadPack\Shopify\Traits\ThrottlesShopifyRequests;
use Throwable;

class ImportSingleProductJob implements ShouldQueue
{
    use Dispatchable;
    use Hookable;
    use InteractsWithQueue;
    use Queueable;
    use SavesImagesAndMetafields;
    use ThrottlesShopifyRequests;

    public $data = [];

    public function __construct(
        public int $productId,
        public ?array $orderData = [],
        public ?string $storeHandle = null,
    ) {
        if ($queue = config('shopify.queue')) {
            $this->onQueue($queue);
        }
    }

    public function handle()
    {
        $graphql = $this->resolveGraphqlClient();

        $variantFields = $this->buildVariantFields();

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
                  {$variantFields}
                }
              }
            }
            vendor
          }
        }
        QUERY;

        $query = $this->runHooksWith('import-product-query', [
            'query' => $query,
            'productId' => $this->productId,
            'storeHandle' => $this->storeHandle,
        ])->query;

        $response = $this->queryWithThrottle($graphql, ['query' => $query]);

        if (! $this->data = Arr::get($response->getDecodedBody(), 'data.product', [])) {
            return;
        }

        $this->data['id'] = Str::afterLast($this->data['id'], '/');

        $productSite = $this->resolveSite();

        $entry = Entry::query()
            ->where('collection', config('shopify.collection_handle', 'products'))
            ->where('site', $productSite)
            ->where('product_id', $this->data['id'])
            ->first();

        if (! config('shopify.import_all_products', true)) {
            $inSalesChannel = collect(Arr::get($this->data, 'resourcePublications.edges', []))
                ->contains(fn ($pub) => Arr::get($pub, 'node.publication.name') === config('shopify.sales_channel', 'Online Store'));

            if (! $inSalesChannel) {
                if ($entry) {
                    $this->removeOldVariants([], $this->data['handle']);
                    $entry->deleteDescendants();
                    $entry->delete();
                }

                return;
            }
        }

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
                ->locale($productSite);
        }

        // Update slug in case it has changed
        $entry->slug($this->data['handle']);

        // Import Variant
        $variantEntries = $this->importVariants($this->data['variants'], $this->data['handle']);

        // Store price/stock summary on the product so listings don't need to query variants
        $data = array_merge($data, ProductSummary::fromVariants($variantEntries));

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
            $data = $this->updatePurchaseHistory($data, (int) array_sum($quantities), $entry);
        }

        $data = $this->runHooksWith('import-product-data', [
            'data' => $data,
            'entry' => $entry,
            'product' => $this->data,
        ])->data;

        $entry->merge($data);

        $published = false;

        // this is to make testing easier
        // means we can just test individual parts of the job
        try {

            // collections (only those already imported as terms)
            try {
                $handles = collect(Arr::get($this->data, 'collections.edges', []))
                    ->map(fn ($collection) => Arr::get($collection, 'node.handle'))
                    ->filter()
                    ->values();

                $existing = $handles->isEmpty() ? collect() : Term::query()
                    ->where('taxonomy', config('shopify.taxonomies.collections'))
                    ->whereIn('slug', $handles->all())
                    ->get()
                    ->map->slug();

                $entry->set(
                    config('shopify.taxonomies.collections'),
                    $handles->filter(fn ($handle) => $existing->contains($handle))->values()->all()
                );
            } catch (Throwable $e) {
                Log::error('Could not retrieve collections for product '.$this->data['id']);
                Log::error($e->getMessage());
            }

            // meta fields
            try {
                $this->syncMetafields($entry, Arr::get($this->data, 'metafields.edges', []), 'product');
            } catch (Throwable $e) {
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
            } catch (Throwable $e) {
                Log::error('Could not manage publications status for product '.$this->data['id']);
                Log::error($e->getMessage());
            }

        } catch (Throwable $e) {
            Log::error($e->getMessage());
        }

        $entry->published($published);
        $entry->save();

        // Skip multi-site translation loop when handling a multi-store job
        // (localized mode uses one store per site; unified mode keeps a single shared entry)
        if ($this->storeHandle) {
            return;
        }

        // if we are multisite, get translations
        if (Site::hasMultiple()) {
            $this->importTranslations($entry, $variantEntries, $graphql);
        }
    }

    /**
     * Fetch translations for the product and all of its variants in one query per site,
     * rather than one query per resource per site.
     */
    private function importTranslations($product, $variants, Graphql $graphql): void
    {
        $productSites = $product->collection()->sites();
        $variantSites = $variants->first()?->collection()->sites() ?? collect();
        $productGid = 'gid://shopify/Product/'.$this->data['id'];

        Site::all()->each(function ($site) use ($product, $variants, $graphql, $productSites, $variantSites, $productGid) {
            if (Site::default()->handle() == $site->handle()) {
                return;
            }

            $resources = collect();

            if ($productSites->contains($site->handle())) {
                $resources->put($productGid, $product);
            }

            if ($variantSites->contains($site->handle())) {
                $variants->each(fn ($variant) => $resources->put('gid://shopify/ProductVariant/'.$variant->slug(), $variant));
            }

            if ($resources->isEmpty()) {
                return;
            }

            $translations = $this->fetchTranslations($graphql, $resources->keys()->all(), $site->lang());

            $resources->each(function ($entry, $gid) use ($site, $translations, $productGid) {
                $rows = $translations[$gid] ?? [];

                if (! $rows && ! $entry->collection()->propagate()) {
                    return;
                }

                $localizedEntry = $entry->in($site->handle()) ?? $entry->makeLocalization($site);

                if ($gid === $productGid) {
                    // All localizations should have the same handle for correct connections to variants
                    $localizedEntry->slug($entry->slug());
                }

                $data = collect($rows)->mapWithKeys(fn ($row) => [
                    ($gid === $productGid && $row['key'] == 'body_html') ? 'content' : $row['key'] => $row['value'],
                ]);

                $localizedEntry->merge($data)->save();
            });
        });
    }

    /**
     * @return array<string, array> translations keyed by resource gid
     */
    private function fetchTranslations(Graphql $graphql, array $resourceIds, string $locale): array
    {
        $query = <<<'QUERY'
            query ($resourceIds: [ID!]!, $first: Int!, $locale: String!) {
              translatableResourcesByIds(first: $first, resourceIds: $resourceIds) {
                nodes {
                  resourceId
                  translations(locale: $locale) {
                    key
                    value
                  }
                }
              }
            }
            QUERY;

        $translations = [];

        foreach (array_chunk($resourceIds, 250) as $chunk) {
            $response = $this->queryWithThrottle($graphql, [
                'query' => $query,
                'variables' => [
                    'resourceIds' => $chunk,
                    'first' => count($chunk),
                    'locale' => $locale,
                ],
            ]);

            foreach (Arr::get($response->getDecodedBody(), 'data.translatableResourcesByIds.nodes', []) as $node) {
                $translations[$node['resourceId']] = $node['translations'] ?? [];
            }
        }

        return $translations;
    }

    /**
     * Build the GraphQL fields for the variant node.
     * In markets mode, appends contextualPricing aliases and inventoryLevels.
     */
    private function buildVariantFields(): string
    {
        $inventoryItemFields = StoreConfig::isMarketsMode()
            ? 'inventoryItem {
                    measurement {
                      weight {
                        unit
                        value
                      }
                    }
                    requiresShipping
                    inventoryLevels(first: 50) {
                      nodes {
                        location {
                          address {
                            countryCode
                          }
                        }
                        quantities(names: ["available"]) {
                          name
                          quantity
                        }
                      }
                    }
                    tracked
                  }'
            : 'inventoryItem {
                    measurement {
                      weight {
                        unit
                        value
                      }
                    }
                    requiresShipping
                    tracked
                  }';

        $contextualPricingFields = '';
        if (StoreConfig::isMarketsMode()) {
            foreach (array_keys(StoreConfig::getMarkets()) as $countryCode) {
                $alias = 'contextualPricing'.strtoupper($countryCode);
                $contextualPricingFields .= <<<GQL

                  {$alias}: contextualPricing(context: { country: {$countryCode} }) {
                    price { amount currencyCode }
                    compareAtPrice { amount currencyCode }
                  }
                GQL;
            }
        }

        return <<<GQL
        compareAtPrice
                  id
                  {$inventoryItemFields}
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
                  title{$contextualPricingFields}
        GQL;
    }

    /**
     * Build the market_data array from contextual pricing and inventory levels.
     */
    private function buildMarketData(array $variant): array
    {
        $marketData = [];

        foreach (array_keys(StoreConfig::getMarkets()) as $countryCode) {
            $alias = 'contextualPricing'.strtoupper($countryCode);
            $price = Arr::get($variant, $alias.'.price.amount');
            $compareAtPrice = Arr::get($variant, $alias.'.compareAtPrice.amount');

            $inventoryNodes = Arr::get($variant, 'inventoryItem.inventoryLevels.nodes', []);
            $inventoryQuantity = collect($inventoryNodes)
                ->filter(fn ($node) => Arr::get($node, 'location.address.countryCode') === $countryCode)
                ->sum(fn ($node) => (int) Arr::get($node, 'quantities.0.quantity', 0));

            $marketData[$countryCode] = [
                'price' => $price,
                'compare_at_price' => $compareAtPrice,
                'inventory_quantity' => $inventoryQuantity,
            ];
        }

        return $marketData;
    }

    /**
     * Resolve the Graphql client for this job.
     * In multi-store mode, uses the store-specific client.
     */
    private function resolveGraphqlClient(): Graphql
    {
        if ($this->storeHandle && StoreConfig::isMultiStore()) {
            $storeConfig = StoreConfig::findByHandle($this->storeHandle);

            if ($storeConfig) {
                return StoreConfig::makeGraphqlClient($storeConfig);
            }
        }

        return app(Graphql::class);
    }

    /**
     * Determine the Statamic site handle to use for this import.
     * In localized multi-store mode, maps the store handle to a site.
     */
    private function resolveSite(): string
    {
        if ($this->storeHandle && StoreConfig::isMultiStore() && StoreConfig::getMode() === 'localized') {
            $storeConfig = StoreConfig::findByHandle($this->storeHandle);

            return $storeConfig['site'] ?? Site::default()->handle();
        }

        return Site::default()->handle();
    }

    private function importTaxonomy(array $tags, string $taxonomyHandle)
    {
        if (! $tags) {
            return null;
        }

        // 'Tag foo, Tag bar' => ['tag-foo' => 'Tag foo', 'tag-bar' => 'Tag bar']
        $tags = collect($tags)
            ->filter()
            ->mapWithKeys(fn ($tagTitle) => [Str::slug($tagTitle) => $tagTitle]);

        if ($tags->isEmpty()) {
            return [];
        }

        $existing = Term::query()
            ->where('taxonomy', $taxonomyHandle)
            ->whereIn('slug', $tags->keys()->all())
            ->get()
            ->map->slug();

        $tags
            ->reject(fn ($tagTitle, $tagSlug) => $existing->contains($tagSlug))
            ->each(fn ($tagTitle, $tagSlug) => Term::make()
                ->taxonomy($taxonomyHandle)
                ->slug($tagSlug)
                ->data(['title' => $tagTitle])
                ->save());

        return $tags->keys()->all();
    }

    /**
     * @return Collection<int, \Statamic\Contracts\Entries\Entry> the saved variant entries
     */
    private function importVariants(array $returnedVariants, string $product_slug)
    {
        $variants = [];
        foreach ($returnedVariants['edges'] as $variant) {
            $variant = $variant['node'];
            $variant['id'] = Str::afterLast($variant['id'], '/');

            $variants[] = $variant;
        }

        $this->removeOldVariants($variants, $product_slug);

        $variantSite = $this->resolveSite();

        $existing = Entry::query()
            ->where('collection', 'variants')
            ->where('site', $variantSite)
            ->whereIn('slug', array_map('strval', array_column($variants, 'id')))
            ->get()
            ->keyBy(fn ($entry) => (string) $entry->slug());

        $saved = collect();

        foreach ($variants as $variant) {
            if (! $entry = $existing->get((string) $variant['id'])) {
                $entry = Entry::make()
                    ->collection('variants')
                    ->locale($variantSite)
                    ->slug($variant['id']);
            }

            // see https://shopify.dev/docs/api/admin-graphql/latest/objects/ProductVariant
            $data = array_merge([
                'variant_id' => (int) $variant['id'],
                'product_slug' => $product_slug,
                'title' => $variant['title'] === 'Default Title' ? 'Default' : $variant['title'],
                'inventory_quantity' => $variant['inventoryQuantity'] ?? null,
                'inventory_policy' => Arr::get($variant, 'inventoryItem.tracked') ? ($variant['inventoryPolicy'] ?? null) : 'CONTINUE',
                'inventory_management' => 'shopify', // @deprecated, left in for backwards JS compatibility
                'price' => $variant['price'],
                'compare_at_price' => $variant['compareAtPrice'],
                'sku' => $variant['sku'],
                'weight' => Arr::get($variant, 'inventoryItem.measurement.weight', null), // blueprint update: was grams, this has unit and value now
                'requires_shipping' => Arr::get($variant, 'inventoryItem.requiresShipping', null),
            ], collect($variant['selectedOptions'] ?? [])->mapWithKeys(fn ($opt, $index) => ['option'.($index + 1) => $opt['value']])->all());  // blueprint update: what if there are more than 3?

            // Markets mode: write territory-specific pricing to market_data
            if (StoreConfig::isMarketsMode()) {
                $data['market_data'] = $this->buildMarketData($variant);
            }

            // Unified multi-store mode: write store-specific pricing to multi_store_data
            if ($this->storeHandle && StoreConfig::isMultiStore() && StoreConfig::getMode() === 'unified') {
                $storeData = [
                    'price' => $variant['price'],
                    'compare_at_price' => $variant['compareAtPrice'],
                    'inventory_quantity' => $variant['inventoryQuantity'] ?? null,
                    'inventory_policy' => Arr::get($variant, 'inventoryItem.tracked') ? ($variant['inventoryPolicy'] ?? null) : 'CONTINUE',
                ];

                $existingMultiStoreData = $entry->get('multi_store_data', []);
                $existingMultiStoreData[$this->storeHandle] = $storeData;
                $data['multi_store_data'] = $existingMultiStoreData;

                // For non-primary stores, do not overwrite the top-level pricing fields
                if (! StoreConfig::isPrimaryStore($this->storeHandle)) {
                    unset($data['price'], $data['compare_at_price'], $data['inventory_quantity'], $data['inventory_policy']);
                }
            }

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

            if ($this->orderData && ($qty = $this->orderedQuantityFor($variant))) {
                $data = $this->updatePurchaseHistory($data, $qty, $entry);
            }

            $entry->merge($data);

            try {
                $this->syncMetafields($entry, Arr::get($variant, 'metafields.edges', []), 'product-variant');
            } catch (Throwable $e) {
                Log::error('Could not retrieve metafields for variant '.$this->data['id']);
            }

            $entry->save();

            $saved->push($entry);
        }

        return $saved;
    }

    /**
     * Remove old variants that are no longer used on a single product.
     */
    private function removeOldVariants(array $variants, string $productSlug)
    {
        $keep = array_map('strval', array_column($variants, 'id'));

        Entry::query()
            ->where('collection', 'variants')
            ->where('product_slug', $productSlug)
            ->where('site', $this->resolveSite())
            ->get()
            ->reject(fn ($variant) => in_array((string) $variant->slug(), $keep, true))
            ->each(function ($variant) {
                // Statamic refuses to delete an entry that still has localizations
                $variant->deleteDescendants();
                $variant->delete();
            });
    }

    /**
     * Handle a job failure — log the error and fire an event.
     */
    public function failed(Throwable $exception): void
    {
        $context = array_filter([
            'product_id' => $this->productId,
            'store' => $this->storeHandle,
        ]);

        Log::error('Shopify: ImportSingleProductJob failed for product '.$this->productId.': '.$exception->getMessage(), $context);

        ProductImportFailed::dispatch($this->productId, $this->storeHandle, $exception);
    }

    /**
     * Sync metafields onto an entry, clearing any keys that were previously set
     * but are no longer returned by Shopify (i.e. the metafield was deleted).
     */
    private function syncMetafields(\Statamic\Contracts\Entries\Entry $entry, array $edges, string $context): void
    {
        $raw = collect($edges)->map(fn ($m) => $m['node'] ?? [])->filter()->all();
        $parsed = $this->parseMetafields($raw, $context);

        $previousKeys = $entry->get('shopify_metafield_keys', []);
        foreach (array_diff($previousKeys, array_keys($parsed)) as $staleKey) {
            $entry->set($staleKey, null);
        }

        if ($parsed) {
            $entry->merge($parsed);
        }

        $entry->set('shopify_metafield_keys', array_keys($parsed));
    }

    /**
     * The quantity ordered of the given variant. Quantities are keyed by variant id,
     * or by SKU for line items without one (and for jobs queued before that change).
     */
    private function orderedQuantityFor(array $variant): int
    {
        $quantities = Arr::get($this->orderData, 'quantity', []);

        if (isset($quantities[$variant['id']])) {
            return (int) $quantities[$variant['id']];
        }

        if (($sku = $variant['sku'] ?? '') !== '' && isset($quantities[$sku])) {
            return (int) $quantities[$sku];
        }

        return 0;
    }

    /**
     * Add to the purchase history for this item, building on the totals already stored on the entry.
     */
    private function updatePurchaseHistory(array $data, int $qty, ?\Statamic\Contracts\Entries\Entry $entry): array
    {
        $date = $this->orderData['date'];

        $totals = $entry?->get('total_purchased') ?? [];
        $yearKey = $date->format('Y').'.total';
        $monthKey = $date->format('Y').'.'.$date->format('m');

        Arr::set($totals, 'lifetime', Arr::get($totals, 'lifetime', 0) + $qty);
        Arr::set($totals, $yearKey, Arr::get($totals, $yearKey, 0) + $qty);
        Arr::set($totals, $monthKey, Arr::get($totals, $monthKey, 0) + $qty);

        $data['total_purchased'] = $totals;
        $data['last_purchased'] = $date->format('Y-m-d H:i:s');

        return $data;
    }
}
