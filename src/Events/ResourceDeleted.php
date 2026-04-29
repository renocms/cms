<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\Models\Resource;

class ResourceDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Resource $resource,
    )
    {
    }
}
