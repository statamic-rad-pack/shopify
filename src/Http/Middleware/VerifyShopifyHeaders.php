<?php

namespace StatamicRadPack\Shopify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Activity\ActivityStatus;
use StatamicRadPack\Shopify\Enums\WebhookTopic;
use StatamicRadPack\Shopify\Support\StoreConfig;
use StatamicRadPack\Shopify\Support\WebhookSubscriptions;

class VerifyShopifyHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('shopify.webhook_secret');

        // In multi-store mode, resolve the store by domain header and use its secret
        if (StoreConfig::isMultiStore() && ($domain = $request->header('X-Shopify-Shop-Domain'))) {
            if (! $store = StoreConfig::findByDomain($domain)) {
                return $this->reject($request, 'Unknown shop domain: '.$domain);
            }

            $request->attributes->set('shopify_store_handle', $store['handle']);
            $secret = $store['webhook_secret'] ?? $secret;
        }

        if (! $this->verify($request->getContent(), $request->header('X-Shopify-Hmac-Sha256'), $secret)) {
            return $this->reject($request, 'Invalid or missing webhook signature');
        }

        $this->recordLastReceived($request);

        // Shopify retries deliveries it doesn't see succeed, and can deliver the same
        // webhook more than once, so skip any delivery we've already handled.
        $deliveryKey = $this->deliveryKey($request);

        if ($deliveryKey && Cache::has($deliveryKey)) {
            ActivityLog::record($this->activityAttributes($request) + ['status' => ActivityStatus::Duplicate->value]);

            return response()->json(['message' => 'Webhook already processed'], 200);
        }

        // controllers and the jobs they queue fill in the outcome on this activity
        $activityId = ActivityLog::record($this->activityAttributes($request) + ['status' => ActivityStatus::Received->value]);
        $request->attributes->set('shopify_activity_id', $activityId);

        $response = $next($request);

        if ($response->isSuccessful()) {
            if ($deliveryKey) {
                Cache::put($deliveryKey, true, now()->addDay());
            }
        } else {
            ActivityLog::update($activityId, [
                'status' => ActivityStatus::Failed->value,
                'message' => 'Webhook handler responded with HTTP '.$response->getStatusCode(),
            ]);
        }

        return $response;
    }

    protected function reject(Request $request, string $reason)
    {
        // the payload isn't trusted, so only the headers are recorded
        ActivityLog::record([
            'source' => Activity::SOURCE_WEBHOOK,
            'status' => ActivityStatus::Rejected->value,
            'topic' => $this->topic($request),
            'store' => $request->attributes->get('shopify_store_handle'),
            'delivery_id' => $request->header('X-Shopify-Webhook-Id'),
            'message' => $reason,
        ]);

        return response()->json(['error' => true], 403);
    }

    protected function activityAttributes(Request $request): array
    {
        $topic = $this->topic($request);
        $payload = json_decode($request->getContent(), true) ?: [];
        $resourceType = $topic ? rtrim(strtok($topic, '/'), 's') : null;

        return [
            'source' => Activity::SOURCE_WEBHOOK,
            'topic' => $topic,
            'store' => $request->attributes->get('shopify_store_handle'),
            'delivery_id' => $request->header('X-Shopify-Webhook-Id'),
            'resource_type' => $resourceType,
            'resource_id' => isset($payload['id']) ? (string) $payload['id'] : null,
            // no customer names or emails in the log, just their id
            'resource_title' => match ($resourceType) {
                'product', 'collection' => $payload['title'] ?? $payload['handle'] ?? null,
                'order' => $payload['name'] ?? null,
                default => null,
            },
        ];
    }

    /**
     * The webhook topic, e.g. products/update, from the header or the route it arrived on.
     */
    protected function topic(Request $request): ?string
    {
        if ($topic = $request->header('X-Shopify-Topic')) {
            return strtolower($topic);
        }

        $routeName = $request->route()?->getName();

        foreach (WebhookTopic::cases() as $case) {
            if ($case->routeName() === $routeName) {
                return $case->topic();
            }
        }

        return null;
    }

    protected function recordLastReceived(Request $request): void
    {
        if (! $rawTopic = $request->header('X-Shopify-Topic')) {
            return;
        }

        WebhookSubscriptions::recordReceived(
            str_replace('/', '_', strtoupper($rawTopic)),
            $request->attributes->get('shopify_store_handle')
        );
    }

    protected function deliveryKey(Request $request): ?string
    {
        if (! $webhookId = $request->header('X-Shopify-Webhook-Id')) {
            return null;
        }

        return 'shopify::webhook_processed::'.$request->header('X-Shopify-Topic').'::'.$webhookId;
    }

    protected function verify(string $data, ?string $hmacHeader, ?string $secret): bool
    {
        if (config('shopify.ignore_webhook_integrity_check', false)) {
            return true;
        }

        if (! $hmacHeader || ! $secret) {
            return false;
        }

        return hash_equals($hmacHeader, base64_encode(hash_hmac('sha256', $data, $secret, true)));
    }
}
