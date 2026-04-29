<?php

namespace Reno\Cms\Http\Resources\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Reno\Cms\Models\Resource;

class ResourceCatalogRowsCollection extends ResourceCollection
{
    public function __construct(
        $resource,
        private readonly array $columns,
    )
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request)
    {
        /** @var Collection<int, Resource> $resources */
        $resources = $this->collection;

        return $resources
            ->map(fn (Resource $resource) => (new ResourceCatalogRowResource(
                $resource,
                $this->columns,
            ))->resolve($request))
            ->all();
    }
}
