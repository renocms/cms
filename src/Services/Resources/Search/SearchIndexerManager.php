<?php

namespace Reno\Cms\Services\Resources\Search;

use Reno\Cms\Interfaces\Services\ResourceSearchIndexerInterface;

class SearchIndexerManager implements ResourceSearchIndexerInterface
{
    public function reindexAll(): int
    {
        return $this->resolveIndexer()->reindexAll();
    }

    public function reindexResource(int $resourceId): void
    {
        $this->resolveIndexer()->reindexResource($resourceId);
    }

    public function reindexResources(array $resourceIds): void
    {
        $this->resolveIndexer()->reindexResources($resourceIds);
    }

    public function deleteResource(int $resourceId): void
    {
        $this->resolveIndexer()->deleteResource($resourceId);
    }

    private function resolveIndexer(): ResourceSearchIndexerInterface
    {
        $driver = (string) config('cms.search.driver', 'database');
        $indexerClass = (string) data_get(config('cms.search.drivers', []), $driver . '.indexer_class', '');

        if ($indexerClass === '') {
            throw new \RuntimeException(sprintf('Search indexer for driver "%s" is not configured', $driver));
        }

        $indexer = resolve($indexerClass);
        if (!$indexer instanceof ResourceSearchIndexerInterface) {
            throw new \RuntimeException(
                sprintf('Search indexer "%s" must implement %s', $indexerClass, ResourceSearchIndexerInterface::class),
            );
        }

        return $indexer;
    }
}
