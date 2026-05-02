<?php

namespace Reno\Cms\Events\Cache;

use Illuminate\Foundation\Events\Dispatchable;
use Reno\Cms\Interfaces\Events\FlushesCmsCache;

class NeedToFlushCmsCache implements FlushesCmsCache
{
    use Dispatchable;
}
