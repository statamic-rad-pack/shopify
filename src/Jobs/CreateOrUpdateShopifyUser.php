<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Auth\User;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use StatamicRadPack\Shopify\Clients\Graphql;
use StatamicRadPack\Shopify\Support\Customers;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Traits\ThrottlesShopifyRequests;

class CreateOrUpdateShopifyUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use ThrottlesShopifyRequests;

    public function __construct(public User $user) {}

    /**
     * Create or update the customer in each store the user belongs to: on multi-store
     * sites, the stores they're linked to (or the default customer store), see Customers.
     */
    public function handle()
    {
        foreach (Customers::syncStores($this->user) as $store) {
            $graphql = StoreConfig::clientFor($store);

            if ($id = Customers::id($this->user, $store)) {
                $this->updateUser($graphql, $id);

                continue;
            }

            $this->createOrAssociateUser($graphql, $store);
        }
    }

    private function createOrAssociateUser(Graphql $graphql, ?string $store)
    {
        $email = $this->user->email();

        $query = <<<'QUERY'
            query ($query: String!) {
              customers(first: 1, query: $query) {
                edges {
                  node {
                    id
                  }
                }
              }
            }
            QUERY;

        $response = $this->queryWithThrottle($graphql, [
            'query' => $query,
            'variables' => [
                'query' => "email:{$email}",
            ],
        ]);

        $id = Arr::get($response->getDecodedBody(), 'data.customers.edges.0.node.id');

        if ($id) {
            Customers::setId($this->user, $store, Str::afterLast($id, '/'))->saveQuietly();

            if (config('shopify.update_users_in_shopify')) {
                $this->updateUser($graphql, $id);
            }

            return;
        }

        $query = <<<'QUERY'
            mutation customerCreate($input: CustomerInput!) {
              customerCreate(input: $input) {
                userErrors {
                  field
                  message
                }
                customer {
                  id
                }
              }
            }
            QUERY;

        $response = $this->queryWithThrottle($graphql, [
            'query' => $query,
            'variables' => [
                'input' => $this->generatePayloadBody(),
            ],
        ]);

        if ($message = $response->firstError('customerCreate')) {
            throw new \Exception($message);
        }

        if ($id = Arr::get($response->getDecodedBody(), 'data.customerCreate.customer.id')) {
            Customers::setId($this->user, $store, Str::afterLast($id, '/'))->saveQuietly();

            return;
        }

        throw new \Exception('Could not create user in Shopify');
    }

    private function updateUser(Graphql $graphql, $id)
    {
        $query = <<<'QUERY'
            mutation customerUpdate($input: CustomerInput!) {
              customerUpdate(input: $input) {
                userErrors {
                  field
                  message
                }
                customer {
                  id
                }
              }
            }
            QUERY;

        $response = $this->queryWithThrottle($graphql, [
            'query' => $query,
            'variables' => [
                'input' => [
                    'id' => str_starts_with((string) $id, 'gid://') ? $id : 'gid://shopify/Customer/'.$id,
                    ...$this->generatePayloadBody(),
                ],
            ],
        ]);

        if ($message = $response->firstError('customerUpdate')) {
            throw new \Exception($message);
        }
    }

    private function generatePayloadBody()
    {
        return [
            'firstName' => Str::before($this->user->name(), ' '),
            'lastName' => Str::after($this->user->name(), ' '),
            'email' => $this->user->email(),
        ];
    }
}
