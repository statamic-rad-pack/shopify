<?php

namespace StatamicRadPack\Shopify\Activity;

enum ActivityStatus: string
{
    /** A webhook was accepted and is being handled */
    case Received = 'received';

    /** An import job has been queued */
    case Queued = 'queued';

    /** Statamic content was created or updated */
    case Updated = 'updated';

    /** Statamic content was deleted */
    case Deleted = 'deleted';

    /** Nothing needed doing (e.g. the product no longer exists in Shopify) */
    case Skipped = 'skipped';

    /** A webhook delivery that had already been processed */
    case Duplicate = 'duplicate';

    /** A webhook that failed verification (bad signature or unknown store) */
    case Rejected = 'rejected';

    case Failed = 'failed';

    public function isFinished(): bool
    {
        return ! in_array($this, [self::Received, self::Queued]);
    }
}
