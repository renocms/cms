<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\Models\Resource;

class ResourceMoving
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Resource $resource,
        public ?int $targetParentId,
        public int $targetSortOrder,
    )
    {
    }
}
