<?php

namespace Reno\Cms\Interfaces\Services;

interface ResourceSearchIndexerInterface
{
    public function reindexAll(): int;

    public function reindexResource(int $resourceId): void;

    public function reindexResources(array $resourceIds): void;

    public function deleteResource(int $resourceId): void;
}
