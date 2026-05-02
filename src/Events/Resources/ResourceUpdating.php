<?php

namespace Reno\Cms\Events\Resources;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\DTO\Resources\ResourceForEdit;
use Reno\Cms\Models\Resource;

class ResourceUpdating
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Resource $resource,
        public ResourceForEdit $resourceForEdit,
    )
    {
    }
}
