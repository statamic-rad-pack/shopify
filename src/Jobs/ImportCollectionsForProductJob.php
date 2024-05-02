<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Shopify\Clients\Graphql;
use Shopify\Clients\Rest;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Support\Arr;
use StatamicRadPack\Shopify\Traits\SavesImagesAndMetafields;

class ImportCollectionsForProductJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SavesImagesAndMetafields;
    use SerializesModels;

    public $product;

    public $collections;

    public function __construct($collections, $product)
    {
        $this->product = $product;
        $this->collections = $collections;
    }

    public function handle()
    {
        $productCollections = collect();

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
            if (isset($collection['image'])) {
                $asset = $this->importImages($collection['image']);
                $data['featured_image'] = $asset->path();
            }

            try {
                $response = app(Rest::class)->get(path: 'metafields', query: ['metafield' => ['owner_id' => $collection['id'], 'owner_resource' => 'collection']]);

                if ($response->getStatusCode() == 200) {
                    $metafields = Arr::get($response->getDecodedBody(), 'metafields', []);

                    if ($metafields) {
                        $metafields = $this->parseMetafields($metafields, 'collection');

                        if ($metafields) {
                            $data = array_merge($metafields, $data);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Could not retrieve metafields for collection '.$collection['id']);
            }

            // if we are multisite, get translations
            if (Site::hasMultiple()) {
                $taxonomySites = $term->taxonomy()->sites();

                Site::all()->each(function ($site) use ($collection, $taxonomySites, $term) {
                    if (Site::default()->handle() == $site->handle()) {
                        return;
                    }

                    if (! $taxonomySites->contains($site->handle())) {
                        return;
                    }

                    $query = <<<QUERY
                      query {
                        translatableResource(resourceId: "gid://shopify/Collection/{$collection['id']}") {
                          resourceId
                          translations(locale: "{$site->locale()}") {
                            key
                            value
                          }
                        }
                      }
                    QUERY;

                    $response = app(Graphql::class)->query(['query' => $query]);

                    $translations = Arr::get($response->getDecodedBody(), 'translatableResource.translatableContent', []);

                    if ($translations) {
                        $localizedEntry = $term->in($site);

                        $data = collect($translations)->mapWithKeys(fn ($row) => [$row['key'] => $row['value']]);

                        $term->dataForLocale($site->handle(), $data->filter()->all());
                    }
                });
            }

            $term->merge($data)->save();

            $productCollections->push($collection['handle']);
        }

        $this->product->set(config('shopify.taxonomies.collections'), $productCollections->toArray());
        $this->product->save();
    }
}
