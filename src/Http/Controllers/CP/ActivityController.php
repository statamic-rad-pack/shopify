<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Facades\Data;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Activity\ActivityStatus;
use StatamicRadPack\Shopify\Contracts\ActivityRepository;
use StatamicRadPack\Shopify\Enums\WebhookTopic;
use StatamicRadPack\Shopify\Support\StoreConfig;

class ActivityController extends CpController
{
    public function index(Request $request)
    {
        $this->authorizeShopify($request);

        return Inertia::render('shopify::Activity', [
            'activityUrl' => cp_route('shopify.activity.data'),
            'dashboardUrl' => cp_route('shopify.index'),
        ]);
    }

    public function data(Request $request, ActivityRepository $activity): JsonResponse
    {
        $this->authorizeShopify($request);

        $paginator = $activity->paginate(
            $request->only(['status', 'source', 'topic', 'store', 'resource_type', 'search']),
            min(100, max(1, (int) $request->get('per_page', 25))),
            max(1, (int) $request->get('page', 1)),
        );

        return response()->json([
            'data' => collect($paginator->items())->map(fn (Activity $item) => $this->present($item))->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'filters' => [
                'statuses' => array_map(fn ($status) => $status->value, ActivityStatus::cases()),
                'sources' => [Activity::SOURCE_WEBHOOK, Activity::SOURCE_CP, Activity::SOURCE_CLI],
                'topics' => array_map(fn ($topic) => strtolower(str_replace('_', '/', $topic->value)), WebhookTopic::cases()),
                'stores' => StoreConfig::isMultiStore() ? array_keys(config('shopify.multi_store.stores', [])) : [],
            ],
        ]);
    }

    public function retry(Request $request, ActivityRepository $activity, string $id): JsonResponse
    {
        $this->authorizeShopify($request);

        if (! $item = $activity->find($id)) {
            abort(404);
        }

        if (! $this->retryable($item)) {
            return response()->json(['message' => __('This activity cannot be retried.')], 422);
        }

        $attributes = ['parent_id' => $item->id, 'resource_title' => $item->resourceTitle, 'message' => __('Retry of a failed import')];

        $newId = $item->resourceType === 'collection'
            ? ActivityLog::queueCollectionImport((int) $item->resourceId, $item->store, Activity::SOURCE_CP, attributes: $attributes)
            : ActivityLog::queueProductImport((int) $item->resourceId, $item->store, Activity::SOURCE_CP, attributes: $attributes);

        return response()->json([
            'message' => __('Import has been queued.'),
            'activity_id' => $newId,
        ]);
    }

    protected function present(Activity $item): array
    {
        return array_merge($item->toArray(), [
            'edit_url' => $item->entryId ? Data::find($item->entryId)?->editUrl() : null,
            'retry_url' => $this->retryable($item) ? cp_route('shopify.activity.retry', $item->id) : null,
        ]);
    }

    protected function retryable(Activity $item): bool
    {
        return $item->status === ActivityStatus::Failed
            && in_array($item->resourceType, ['product', 'collection'])
            && is_numeric($item->resourceId);
    }

    protected function authorizeShopify(Request $request): void
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }
    }
}
