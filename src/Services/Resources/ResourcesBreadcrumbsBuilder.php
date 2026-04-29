<?php

namespace Reno\Cms\Services\Resources;

use Reno\Cms\Models\Resource;
use Illuminate\Support\Collection;
use Reno\Cms\Containers\BreadcrumbContainer;
use Reno\Cms\Interfaces\Resources\ResourceInterface;
use Reno\Cms\Interfaces\Repositories\SettingRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\ResourceRepositoryInterface;
use Reno\Cms\Interfaces\Services\ResourcesBreadcrumbsBuilderInterface;

class ResourcesBreadcrumbsBuilder implements ResourcesBreadcrumbsBuilderInterface
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
        private ResourceRepositoryInterface $resourceRepository,
    )
    {
    }

    public function build(ResourceInterface $resource): Collection
    {
        $resourceIds = $resource->getParentIds();
        $resources = $this->resourceRepository->get($resourceIds)
            ->sortBy(fn (Resource $res) => array_search($res->id, $resourceIds));
        $resources->prepend($resource);

        $homeResource = $this->getHomeResource();
        $hasHomeResource = false;

        $result = Collection::make();

        foreach ($resources as $breadcrumbResource) {
            $result->push(new BreadcrumbContainer(
                resource: $breadcrumbResource,
                isHome: $breadcrumbResource->getId() == $homeResource->getId(),
                isCurrent: $breadcrumbResource->getId() == $resource->getId(),
            ));

            $hasHomeResource = $hasHomeResource || $breadcrumbResource->getId() == $homeResource->getId();
        }

        if (!$hasHomeResource) {
            $result->add(new BreadcrumbContainer(
                resource: $homeResource,
                isHome: true,
                isCurrent: $homeResource->getId() == $resource->getId(),
            ));
        }

        return $result->reverse();
    }

    private function getHomeResource(): ResourceInterface
    {
        $homeResourceId = $this->settingRepository->getHomeResourceId(app('cms.current_context_id'));
        return $this->resourceRepository->findById($homeResourceId);
    }
}
