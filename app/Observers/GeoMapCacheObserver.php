<?php

namespace App\Observers;

use App\Support\GeoMapCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class GeoMapCacheObserver implements ShouldHandleEventsAfterCommit
{
    public function created(object $model): void
    {
        GeoMapCache::bumpVersion();
    }

    public function updated(object $model): void
    {
        GeoMapCache::bumpVersion();
    }

    public function deleted(object $model): void
    {
        GeoMapCache::bumpVersion();
    }

    public function restored(object $model): void
    {
        GeoMapCache::bumpVersion();
    }

    public function forceDeleted(object $model): void
    {
        GeoMapCache::bumpVersion();
    }
}
