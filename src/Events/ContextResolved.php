<?php

namespace Reno\Cms\Events;

use Reno\Cms\Containers\ContextContainer;
use Illuminate\Foundation\Events\Dispatchable;
use Reno\Cms\Interfaces\Events\FlushesCmsCache;

class ContextResolved implements FlushesCmsCache
{
    use Dispatchable;

    public function __construct(
        public ContextContainer $contextContainer,
    )
    {
    }
}
