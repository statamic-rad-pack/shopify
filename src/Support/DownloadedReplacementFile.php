<?php

namespace StatamicRadPack\Shopify\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\ReplacementFile;

/**
 * An image downloaded from Shopify, for Statamic's Asset::reupload(). Statamic's
 * ReplacementFile reads from the CP upload disk; downloads go to the local disk.
 */
class DownloadedReplacementFile extends ReplacementFile
{
    public function writeTo(Filesystem $disk, $path)
    {
        $disk->put($path, Storage::disk('local')->readStream($this->path()));
    }
}
