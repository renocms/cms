<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\Models\Resource;

class ResourceMoved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Resource $resource,
        public ?int $oldParentId,
        public ?int $newParentId,
    )
    {
    }
}
