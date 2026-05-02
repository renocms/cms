<?php

namespace Reno\Cms\Services\Resources\Search;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Query\Builder;
use Reno\Cms\DTO\Resources\ResourceSearchCriteria;
use Reno\Cms\Interfaces\Services\ResourceSearchEngineInterface;

class SearchEngineManager implements ResourceSearchEngineInterface
{
    public function __construct(
        private readonly Container $container,
    )
    {
    }

    public function makeSearchSubquery(ResourceSearchCriteria $criteria): Builder
    {
        $driver = (string) config('cms.search.driver', 'database');
        $engineClass = (string) data_get(config('cms.search.drivers', []), $driver . '.engine_class', '');
        if ($engineClass === '') {
            throw new \RuntimeException(
                sprintf('Search engine driver "%s" is not configured', $driver),
            );
        }

        $engine = $this->container->make($engineClass);
        if (!$engine instanceof ResourceSearchEngineInterface) {
            throw new \RuntimeException(
                sprintf('Search engine "%s" must implement %s', $engineClass, ResourceSearchEngineInterface::class),
            );
        }

        return $engine->makeSearchSubquery($criteria);
    }
}
