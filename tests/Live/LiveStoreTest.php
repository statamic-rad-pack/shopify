<?php

namespace StatamicRadPack\Shopify\Tests\Live;

use Dotenv\Dotenv;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Jobs\ImportCollectionJob;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Support\WebhookSubscriptions;
use StatamicRadPack\Shopify\Tests\TestCase;

/**
 * Smoke tests against a real (development) Shopify store.
 *
 * Skipped unless a .env.live file exists in the package root with SHOPIFY_APP_URL,
 * SHOPIFY_ADMIN_TOKEN and SHOPIFY_STOREFRONT_TOKEN. Excluded from the default run;
 * run with: vendor/bin/phpunit -c phpunit.xml --group live
 *
 * The address test creates, updates and deletes an address on a real customer.
 */
#[Group('live')]
class LiveStoreTest extends TestCase
{
    private static ?array $live = null;

    private RecordingGraphql $graphql;

    private string $assetPath;

    private static function liveConfig(): array
    {
        if (self::$live === null) {
            $file = __DIR__.'/../../.env.live';
            self::$live = file_exists($file) ? Dotenv::parse(file_get_contents($file)) : [];
        }

        return self::$live;
    }

    protected function defineEnvironment($app)
    {
        $live = self::liveConfig();

        $app['config']->set('shopify.url', $live['SHOPIFY_APP_URL'] ?? null);
        $app['config']->set('shopify.admin_token', $live['SHOPIFY_ADMIN_TOKEN'] ?? null);
        $app['config']->set('shopify.storefront_token', $live['SHOPIFY_STOREFRONT_TOKEN'] ?? null);

        if (isset($live['SHOPIFY_API_VERSION'])) {
            $app['config']->set('shopify.api_version', $live['SHOPIFY_API_VERSION']);
        }

        $this->assetPath = sys_get_temp_dir().'/shopify-live-assets-'.getmypid();
        $app['config']->set('filesystems.disks.shopify_live', ['driver' => 'local', 'root' => $this->assetPath]);
    }

    protected function setUp(): void
    {
        $live = self::liveConfig();

        if (empty($live['SHOPIFY_APP_URL']) || empty($live['SHOPIFY_ADMIN_TOKEN'])) {
            $this->markTestSkipped('No .env.live with SHOPIFY_APP_URL and SHOPIFY_ADMIN_TOKEN');
        }

        parent::setUp();

        $this->graphql = new RecordingGraphql(config('shopify.url'), config('shopify.admin_token'));
        $this->app->instance(Graphql::class, $this->graphql);

        Facades\AssetContainer::make(config('shopify.asset.container'))->disk('shopify_live')->save();

        foreach (config('shopify.taxonomies') as $handle) {
            Facades\Taxonomy::make($handle)->save();
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->graphql)) {
            fwrite(STDERR, sprintf(
                "\n  [%s] %d Admin API calls, highest requested cost %s, highest actual cost %s",
                $this->name(),
                count($this->graphql->responses),
                $this->graphql->maxCost('requestedQueryCost') ?? 'n/a',
                $this->graphql->maxCost('actualQueryCost') ?? 'n/a',
            ));
        }

        if (isset($this->assetPath) && is_dir($this->assetPath)) {
            (new Filesystem)->deleteDirectory($this->assetPath);
        }

        parent::tearDown();
    }

    #[Test]
    public function the_configured_api_version_is_accepted()
    {
        $body = $this->graphql->query(['query' => '{ shop { name } }'])->getDecodedBody();

        $this->assertNotEmpty(Arr::get($body, 'data.shop.name'), json_encode($body));
        $this->assertNoGraphqlErrors();
    }

    #[Test]
    public function imports_a_product_with_its_variants_and_summary()
    {
        $product = $this->findProduct();

        ImportSingleProductJob::dispatchSync($product['id']);

        $this->assertNoGraphqlErrors();

        $entry = Facades\Entry::query()
            ->where('collection', config('shopify.collection_handle', 'products'))
            ->where('product_id', (string) $product['id'])
            ->first();

        $this->assertNotNull($entry, 'Product entry was not created');
        $this->assertSame($product['handle'], $entry->slug());

        $variants = Facades\Entry::query()->where('collection', 'variants')->where('product_slug', $entry->slug())->get();
        $this->assertCount($product['variantsCount'], $variants, 'Not every variant was imported');

        $this->assertNotNull($entry->get('price_min'));
        $this->assertIsBool($entry->get('in_stock'));
        $this->assertIsBool($entry->get('on_sale'));

        if ($product['mediaCount'] > 0) {
            $this->assertNotEmpty($entry->get('gallery'), 'No images were imported');
        }
    }

    #[Test]
    public function imports_translations_for_a_secondary_locale()
    {
        $locales = Arr::get($this->graphql->query(['query' => '{ shopLocales { locale primary published } }'])->getDecodedBody(), 'data.shopLocales', []);
        $secondary = collect($locales)->first(fn ($locale) => ! $locale['primary']);

        // a missing access scope should fail, not skip
        $this->assertNoGraphqlErrors();

        if (! $secondary) {
            $this->markTestSkipped('The store has no secondary locale');
        }

        $lang = Str::before($secondary['locale'], '-');

        Facades\Site::setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            $lang => ['url' => '/'.$lang.'/', 'locale' => $lang],
        ]);

        Facades\Collection::make(config('shopify.collection_handle', 'products'))->sites(['en', $lang])->save();
        Facades\Collection::make('variants')->sites(['en', $lang])->save();

        ImportSingleProductJob::dispatchSync($this->findProduct()['id']);

        $this->assertNoGraphqlErrors();

        $nodes = collect($this->graphql->responses)
            ->filter(fn ($response) => str_contains($response['query'], 'translatableResourcesByIds'))
            ->flatMap(fn ($response) => Arr::get($response['body'], 'data.translatableResourcesByIds.nodes', []));

        $this->assertNotEmpty($nodes, 'Translations were not requested');

        // anything translated in Shopify should land on the localized entries
        $productTitle = collect($nodes->firstWhere(fn ($node) => str_contains($node['resourceId'], '/Product/'))['translations'] ?? [])
            ->firstWhere('key', 'title')['value'] ?? null;
        $optionValues = $nodes
            ->filter(fn ($node) => str_contains($node['resourceId'], '/ProductOptionValue/'))
            ->flatMap(fn ($node) => collect($node['translations'])->where('key', 'name')->pluck('value'));

        fwrite(STDERR, sprintf("\n  [translations] product title %s, %d option value(s) translated", $productTitle ? 'translated' : 'not translated', $optionValues->count()));

        if ($productTitle) {
            $this->assertSame($productTitle, Facades\Entry::whereCollection(config('shopify.collection_handle', 'products'))->firstWhere('locale', $lang)?->get('title'));
        }

        if ($optionValues->isNotEmpty()) {
            $variantOptions = Facades\Entry::whereCollection('variants')
                ->where('locale', $lang)
                ->flatMap(fn ($variant) => [$variant->get('option1'), $variant->get('option2'), $variant->get('option3')])
                ->filter();

            $this->assertNotEmpty($variantOptions->intersect($optionValues), 'Translated option values were not applied to variants');
        }
    }

    #[Test]
    public function imports_a_collection()
    {
        $id = Arr::get($this->graphql->query(['query' => '{ collections(first: 1) { nodes { id } } }'])->getDecodedBody(), 'data.collections.nodes.0.id');

        $this->assertNoGraphqlErrors();

        if (! $id) {
            $this->markTestSkipped('The store has no collections');
        }

        ImportCollectionJob::dispatchSync((int) Str::afterLast($id, '/'));

        $this->assertNoGraphqlErrors();
        $this->assertNotNull(Facades\Term::query()->where('taxonomy', config('shopify.taxonomies.collections'))->first());
    }

    #[Test]
    public function renders_customer_details_addresses_and_paginated_orders()
    {
        [$customerId, $orderCount] = $this->findCustomerWithOrders();

        $this->actingAs(tap(Facades\User::make()->email('live-test@example.com')->set('shopify_id', $customerId))->save());

        $email = $this->tag('{{ shopify:customer }}{{ email }}{{ /shopify:customer }}');
        $this->assertNotSame('', trim($email), 'Customer details were not returned');

        $this->tag('{{ shopify:customer:addresses }}{{ addresses_count }}{{ /shopify:customer:addresses }}');

        $output = $this->tag('{{ shopify:customer:orders paginate="1" }}{{ orders }}{{ name }}{{ /orders }}|{{ orders_count }}{{ /shopify:customer:orders }}');

        $this->assertNoGraphqlErrors();
        $this->assertStringStartsWith('#', $output, 'No order was rendered');
        $this->assertSame((string) $orderCount, Str::afterLast($output, '|'), 'orders_count does not match the customer');
    }

    #[Test]
    public function creates_updates_and_deletes_a_customer_address()
    {
        $customerId = $this->findAnyCustomer();

        $this->actingAs(tap(Facades\User::make()->email('live-test@example.com')->set('shopify_id', $customerId))->save());

        $address = [
            'firstName' => 'Live',
            'lastName' => 'Test',
            'address1' => '1 Test Street',
            'city' => 'Los Angeles',
            'provinceCode' => 'CA',
            'zip' => '90001',
            'countryCode' => 'US',
        ];

        $created = $this->postJson('/!/shopify/address', $address)->assertOk()->json('address');
        $this->assertNotEmpty($created['id'] ?? null, 'Address was not created');

        try {
            $this->postJson('/!/shopify/address/'.$created['id'], ['address2' => 'Suite 2'] + $address)
                ->assertOk()
                ->assertJsonPath('address.address2', 'Suite 2');
        } finally {
            $this->deleteJson('/!/shopify/address/'.$created['id'])->assertOk();
        }

        $this->assertNoGraphqlErrors();
    }

    #[Test]
    public function reads_webhook_subscriptions()
    {
        $this->assertIsArray(WebhookSubscriptions::fetch($this->graphql));
        $this->assertNoGraphqlErrors();
    }

    #[Test]
    public function the_storefront_cart_query_used_by_cart_js_is_valid()
    {
        $live = self::liveConfig();

        if (empty($live['SHOPIFY_STOREFRONT_TOKEN'])) {
            $this->markTestSkipped('No SHOPIFY_STOREFRONT_TOKEN');
        }

        $storefront = fn (string $query, array $variables = []) => Http::withHeaders([
            'X-Shopify-Storefront-Access-Token' => $live['SHOPIFY_STOREFRONT_TOKEN'],
        ])->post('https://'.Graphql::shop(config('shopify.url')).'.myshopify.com/api/'.config('shopify.api_version').'/graphql.json', [
            'query' => $query,
            'variables' => $variables ?: new \stdClass,
        ])->throw()->json();

        $variantId = Arr::get($storefront('{ products(first: 1) { nodes { variants(first: 1) { nodes { id } } } } }'), 'data.products.nodes.0.variants.nodes.0.id');

        if (! $variantId) {
            $this->markTestSkipped('No products are available to the storefront');
        }

        // the same fields resources/js/shopify/cart.js requests
        $cartFields = 'id checkoutUrl cost { totalAmount { amount currencyCode } } lines(first: 250) { edges { node { attributes { key value } cost { amountPerQuantity { amount } } id merchandise { ... on ProductVariant { id image { url } title product { title } sku } } quantity } } } note';

        $body = $storefront(
            'mutation createCart($cartInput: CartInput) { cartCreate(input: $cartInput) { cart { '.$cartFields.' } userErrors { field message } } }',
            ['cartInput' => ['lines' => [['merchandiseId' => $variantId, 'quantity' => 1]]]]
        );

        $this->assertArrayNotHasKey('errors', $body, json_encode($body['errors'] ?? null));
        $this->assertEmpty(Arr::get($body, 'data.cartCreate.userErrors'), json_encode(Arr::get($body, 'data.cartCreate.userErrors')));
        $this->assertNotEmpty(Arr::get($body, 'data.cartCreate.cart.checkoutUrl'));
    }

    /**
     * The most recently updated product with the most variants (of the latest 20).
     *
     * @return array{id: int, handle: string, variantsCount: int, mediaCount: int}
     */
    private function findProduct(): array
    {
        $nodes = Arr::get($this->graphql->query(['query' => '{ products(first: 20, sortKey: UPDATED_AT, reverse: true) { nodes { id handle variantsCount { count } mediaCount { count } } } }'])->getDecodedBody(), 'data.products.nodes', []);

        if (! $nodes) {
            $this->markTestSkipped('The store has no products');
        }

        $node = collect($nodes)->sortByDesc(fn ($node) => $node['variantsCount']['count'])->first();

        return [
            'id' => (int) Str::afterLast($node['id'], '/'),
            'handle' => $node['handle'],
            'variantsCount' => $node['variantsCount']['count'],
            'mediaCount' => $node['mediaCount']['count'],
        ];
    }

    /**
     * @return array{0: int, 1: int} customer id and order count
     */
    private function findCustomerWithOrders(): array
    {
        // go via orders: the customer search index can lag behind new orders
        $customers = collect(Arr::get($this->graphql->query(['query' => '{ orders(first: 50, sortKey: CREATED_AT, reverse: true) { nodes { customer { id } } } }'])->getDecodedBody(), 'data.orders.nodes', []))
            ->pluck('customer.id')
            ->filter()
            ->countBy()
            ->sortDesc();

        $this->assertNoGraphqlErrors();

        if ($customers->isEmpty()) {
            $this->markTestSkipped('The store has no orders with a customer');
        }

        $id = $customers->keys()->first();

        // the total the tag should report, counted the same way the tag queries orders
        $count = Arr::get($this->graphql->query([
            'query' => 'query ($search: String) { ordersCount(query: $search, limit: null) { count } }',
            'variables' => ['search' => 'customer_id:'.Str::afterLast($id, '/')],
        ])->getDecodedBody(), 'data.ordersCount.count');

        return [(int) Str::afterLast($id, '/'), (int) $count];
    }

    private function findAnyCustomer(): int
    {
        $id = Arr::get($this->graphql->query(['query' => '{ customers(first: 1) { nodes { id } } }'])->getDecodedBody(), 'data.customers.nodes.0.id');

        $this->assertNoGraphqlErrors();

        if (! $id) {
            $this->markTestSkipped('The store has no customers');
        }

        return (int) Str::afterLast($id, '/');
    }

    private function assertNoGraphqlErrors(): void
    {
        foreach ($this->graphql->responses as $response) {
            $this->assertArrayNotHasKey('errors', $response['body'], sprintf(
                "GraphQL errors for query:\n%s\n%s",
                Str::limit(trim($response['query']), 300),
                json_encode($response['body']['errors'] ?? null, JSON_PRETTY_PRINT)
            ));
        }
    }

    private function tag(string $tag, array $variables = []): string
    {
        return (string) Facades\Parse::template($tag, $variables, trusted: true);
    }
}

/**
 * The real client, recording every response so tests can check for errors and query cost.
 */
class RecordingGraphql extends Graphql
{
    public array $responses = [];

    public function query(array $params): HttpResponse
    {
        $response = parent::query($params);

        $this->responses[] = [
            'query' => $params['query'] ?? '',
            'body' => $response->getDecodedBody(),
        ];

        return $response;
    }

    public function maxCost(string $key): ?float
    {
        return collect($this->responses)->map(fn ($response) => Arr::get($response['body'], 'extensions.cost.'.$key))->filter()->max();
    }
}
