<?php

namespace Reno\Cms\Events\Resources;

use Illuminate\Foundation\Events\Dispatchable;
use Reno\Cms\Containers\ResourceLayoutContainer;

class ResourceLayoutResolved
{
    use Dispatchable;

    public function __construct(
        ResourceLayoutContainer $resourceLayoutContainer,
    )
    {
    }
}
