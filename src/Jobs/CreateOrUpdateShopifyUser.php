<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shopify\Clients\Graphql;
use Statamic\Contracts\Auth\User;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class CreateOrUpdateShopifyUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public User $user) {}

    public function handle()
    {
        if (! $id = $this->user->get('shopify_id')) {
            $this->createOrAssociateUser();

            return;
        }

        $this->updateUser($id);
    }

    private function createOrAssociateUser()
    {
        $email = $this->user->email();

        $query = <<<'QUERY'
            query {
              customers(first: 1, query: "email:{$email}") {
                edges {
                  node {
                    id
                  }
                }
              }
            }
            QUERY;

        $response = app(Graphql::class)->query([
            'query' => $query,
        ]);

        $id = Arr::get($response->getDecodedBody(), 'data.customers.edges.0.node.id');

        if ($id) {
            $this->user->set('shopify_id', (int) Str::afterLast($id, '/'));
            $this->user->saveQuietly();

            if (config('shopify.update_users_in_shopify')) {
                $this->updateUser($id);
            }

            return;
        }

        $query = <<<'QUERY'
            mutation customerCreate(\$input: CustomerInput!) {
              customerCreate(input: \$input) {
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

        $response = app(Graphql::class)->query([
            'query' => $query,
            'variables' => [
                'input' => $this->generatePayloadBody(),
            ],
        ]);

        $body = $response->getDecodedBody();

        if ($id = Arr::get($body, 'data.customerCreate.customer.id', false)) {
            $this->user->set('shopify_id', (int) Str::afterLast($id, '/'));
            $this->user->saveQuietly();

            return;
        }

        if ($message = Arr::get($body, 'data.customerCreate.userErrors.message')) {
            throw new \Exception($message);
        }

        throw new \Exception('Could not create user in Shopify');
    }

    private function updateUser($id)
    {
        $query = <<<'QUERY'
            mutation customerUpdate(\$input: CustomerInput!) {
              customerUpdate(input: \$input) {
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

        $response = app(Graphql::class)->query([
            'query' => $query,
            'variables' => [
                'input' => $this->generatePayloadBody(),
            ],
        ]);

        $body = $response->getDecodedBody();

        if ($message = Arr::get($body, 'customerUpdate.userErrors.message')) {
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
