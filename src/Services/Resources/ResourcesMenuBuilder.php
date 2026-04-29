<?php

namespace Reno\Cms\Services\Resources;

use Reno\Cms\Models\Resource;
use Illuminate\Support\Collection;
use Reno\Cms\Containers\MenuItemContainer;
use Reno\Cms\DTO\Resources\ResourceTreeBuilderParams;
use Reno\Cms\Interfaces\Services\ResourcesMenuBuilderInterface;
use Reno\Cms\Interfaces\Services\ResourcesTreeBuilderInterface;

class ResourcesMenuBuilder implements ResourcesMenuBuilderInterface
{
    public function __construct(
        protected ResourcesTreeBuilderInterface $resourcesTreeBuilder,
        protected Resource $resource,
    )
    {
    }

    public function build(ResourceTreeBuilderParams $params): Collection
    {
        $resources = $this->resourcesTreeBuilder->getTree($params);

        $activeIds = array_fill_keys($this->resource->getParentIds(), true);
        $activeIds[$this->resource->id] = true;

        return $this->wrapResources($resources, $this->resource->id, $activeIds);
    }

    private function wrapResources(Collection $resources, int $currentId, array $activeIds): Collection
    {
        return $resources->map(function (Resource $resource) use ($currentId, $activeIds) {
            return new MenuItemContainer(
                resource: $resource,
                isCurrent: $resource->id == $currentId,
                isActive: isset($activeIds[$resource->id]),
                children: $resource->relationLoaded('children') && $resource->children->isNotEmpty()
                    ? $this->wrapResources($resource->children, $currentId, $activeIds)
                    : Collection::make(),
            );
        });
    }
}
