<?php

namespace Reno\Cms\Listeners;

use Illuminate\Support\Facades\Event;
use Reno\Cms\Events\Resources\CmsCacheFlushed;
use Reno\Cms\Interfaces\Events\FlushesCmsCache;
use Reno\Cms\Services\CmsCache;

class FlushCmsCache
{
    public function handle(FlushesCmsCache $_event): void
    {
        CmsCache::flush();

        Event::dispatch(new CmsCacheFlushed());
    }
}
