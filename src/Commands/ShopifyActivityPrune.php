<?php

namespace StatamicRadPack\Shopify\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use StatamicRadPack\Shopify\Contracts\ActivityRepository;

class ShopifyActivityPrune extends Command
{
    use RunsInPlease;

    protected $signature = 'shopify:activity:prune {--days= : Keep this many days of activity (defaults to shopify.activity.retention_days)}';

    protected $description = 'Delete Shopify activity log entries older than the retention period';

    public function handle(ActivityRepository $activity): int
    {
        $days = (int) ($this->option('days') ?? config('shopify.activity.retention_days', 30));

        $removed = $activity->prune(now()->subDays($days));

        $this->info("Removed {$removed} Shopify activity entries older than {$days} days.");

        return self::SUCCESS;
    }
}
