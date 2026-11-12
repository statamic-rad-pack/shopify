<?php

namespace StatamicRadPack\Shopify\Activity;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Support\Str;

/**
 * One entry in the Shopify activity log: a webhook delivery or an import.
 */
class Activity implements Arrayable
{
    public const SOURCE_WEBHOOK = 'webhook';

    public const SOURCE_CP = 'cp';

    public const SOURCE_CLI = 'cli';

    public function __construct(
        public string $id,
        public string $source,
        public ActivityStatus $status,
        public ?string $topic = null,
        public ?string $store = null,
        public ?string $resourceType = null,
        public ?string $resourceId = null,
        public ?string $resourceTitle = null,
        public ?string $entryId = null,
        public ?string $message = null,
        public array $changes = [],
        public ?string $deliveryId = null,
        public ?string $parentId = null,
        public ?CarbonImmutable $createdAt = null,
        public ?CarbonImmutable $updatedAt = null,
    ) {
        $this->createdAt ??= CarbonImmutable::now();
        $this->updatedAt ??= $this->createdAt;
    }

    /**
     * Ids sort by creation time and contain the date, so storage can be partitioned by day.
     */
    public static function newId(?CarbonImmutable $at = null): string
    {
        return ($at ?? CarbonImmutable::now())->format('Ymd-His-v').'-'.Str::lower(Str::random(8));
    }

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            source: $data['source'],
            status: $data['status'] instanceof ActivityStatus ? $data['status'] : ActivityStatus::from($data['status']),
            topic: $data['topic'] ?? null,
            store: $data['store'] ?? null,
            resourceType: $data['resource_type'] ?? null,
            resourceId: isset($data['resource_id']) ? (string) $data['resource_id'] : null,
            resourceTitle: $data['resource_title'] ?? null,
            entryId: $data['entry_id'] ?? null,
            message: $data['message'] ?? null,
            changes: is_string($data['changes'] ?? null) ? (json_decode($data['changes'], true) ?: []) : ($data['changes'] ?? []),
            deliveryId: $data['delivery_id'] ?? null,
            parentId: $data['parent_id'] ?? null,
            createdAt: isset($data['created_at']) ? CarbonImmutable::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? CarbonImmutable::parse($data['updated_at']) : null,
        );
    }

    /**
     * Apply snake_case attributes (as accepted by ActivityRepository::update).
     */
    public function fill(array $attributes): static
    {
        $data = array_merge($this->toArray(), $attributes, ['updated_at' => CarbonImmutable::now()->toIso8601String()]);

        return static::fromArray($data);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'source' => $this->source,
            'status' => $this->status->value,
            'topic' => $this->topic,
            'store' => $this->store,
            'resource_type' => $this->resourceType,
            'resource_id' => $this->resourceId,
            'resource_title' => $this->resourceTitle,
            'entry_id' => $this->entryId,
            'message' => $this->message,
            'changes' => $this->changes,
            'delivery_id' => $this->deliveryId,
            'parent_id' => $this->parentId,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
        ];
    }
}
