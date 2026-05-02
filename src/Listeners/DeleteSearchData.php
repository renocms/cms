<?php

namespace Reno\Cms\Listeners;

use Reno\Cms\Interfaces\Services\ResourceSearchIndexerInterface;

class DeleteSearchData
{
    public function __construct(
        private readonly ResourceSearchIndexerInterface $resourceSearchIndexer,
    )
    {
    }

    public function handle(object $event): void
    {
        if (!isset($event->resource)) {
            return;
        }

        $resourceId = (int) $event->resource->id;
        if ($resourceId <= 0) {
            return;
        }

        $this->resourceSearchIndexer->deleteResource($resourceId);
    }
}
