<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Shopify\Clients\Graphql;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use StatamicRadPack\Shopify\Traits\SavesImagesAndMetafields;

class ImportCollectionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SavesImagesAndMetafields;

    private array $collection = [];

    public function __construct(public int $collectionId)
    {
        if ($queue = config('shopify.queue')) {
            $this->onQueue($queue);
        }
    }

    public function handle()
    {
        $query = <<<QUERY
            {
              collection(id: "gid://shopify/Collection/{$this->collectionId}") {
                descriptionHtml
                handle
                id
                image {
                  url
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
                title
              }
            }
            QUERY;

        $response = app(Graphql::class)->query(['query' => $query]);

        if (! $this->collection = Arr::get($response->getDecodedBody(), 'data.collection', [])) {
            return;
        }

        $term = Term::query()
            ->where('slug', $this->collection['handle'])
            ->where('taxonomy', config('shopify.taxonomies.collections'))
            ->first();

        if (! $term) {
            $term = Term::make()
                ->taxonomy(config('shopify.taxonomies.collections'))
                ->slug($this->collection['handle']);
        }

        $data = [
            'title' => $this->collection['title'],
            'collection_id' => (int) Str::afterLast($this->collection['id'], '/'),
            'content' => $this->collection['descriptionHtml'],
        ];

        // Import Images
        if (isset($this->collection['image'])) {
            if ($asset = $this->importImages($this->collection['image'])) {
                $data['featured_image'] = $asset->path();
            }
        }

        $metafields = collect(Arr::get($this->collection, 'metafields.edges', []))->map(fn ($metafield) => $metafield['node'] ?? [])->filter()->all();

        if ($metafields) {
            $metafields = $this->parseMetafields($metafields, 'collection');

            if ($metafields) {
                $data = array_merge($metafields, $data);
            }
        }

        // if we are multisite, get translations
        if (Site::hasMultiple()) {
            $taxonomySites = $term->taxonomy()->sites();

            Site::all()->each(function ($site) use ($taxonomySites, $term) {
                if (Site::default()->handle() == $site->handle()) {
                    return;
                }

                if (! $taxonomySites->contains($site->handle())) {
                    return;
                }

                $query = <<<QUERY
                  query {
                    translatableResource(resourceId: "gid://shopify/Collection/{$this->collection['id']}") {
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
                    $data = collect($translations)->mapWithKeys(fn ($row) => [$row['key'] => $row['value']]);

                    $term->dataForLocale($site->handle(), $data->filter()->all());
                }
            });
        }

        $term->merge($data)->save();
    }
}
