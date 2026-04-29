<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Reno\Cms\DTO\Resources\ResourceForCreate;

class ResourceCreating
{
    use Dispatchable;

    public function __construct(
        public ResourceForCreate $resourceForCreate,
    )
    {
    }
}
