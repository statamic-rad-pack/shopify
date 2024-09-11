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

class ImportCollectionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SavesImagesAndMetafields;
    use SerializesModels;

    public function __construct(public array $collection) {}

    public function handle()
    {
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
            'collection_id' => $this->collection['id'],
            'content' => $this->collection['body_html'],
        ];

        // Import Images
        if (isset($this->collection['image'])) {
            $asset = $this->importImages($this->collection['image']);
            $data['featured_image'] = $asset->path();
        }

        try {
            $response = app(Rest::class)->get(path: 'metafields', query: ['metafield' => ['owner_id' => $this->collection['id'], 'owner_resource' => 'collection']]);

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
            Log::error('Could not retrieve metafields for collection '.$this->collection['id']);
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
                    $localizedEntry = $term->in($site);

                    $data = collect($translations)->mapWithKeys(fn ($row) => [$row['key'] => $row['value']]);

                    $term->dataForLocale($site->handle(), $data->filter()->all());
                }
            });
        }

        $term->merge($data)->save();
    }
}
