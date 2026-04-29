<?php

namespace Reno\Cms\Repositories;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Reno\Cms\Services\Resources\ResourcesHydrator;
use Reno\Cms\Interfaces\Repositories\ResourceRepositoryInterface;
use Reno\Cms\Interfaces\Resources\ResourceInterface;
use Reno\Cms\Models\Resource;

class ResourceRepository implements ResourceRepositoryInterface
{
    public function __construct(
        protected ResourcesHydrator $resourcesHydrator,
    )
    {
    }

    public function getTreeByContext(int $contextId, array $expandedIds = []): Collection
    {
        $query = Resource::query()
            ->where('context_id', $contextId);
            
        if (empty($expandedIds)) {
            $query->whereNull('parent_id');
        } else {
            $query->where(function (Builder $query) use ($expandedIds) {
                $query->whereNull('parent_id')
                    ->orWhereIn('id', $expandedIds)
                    ->orWhereIn('parent_id', $expandedIds);
            });
        }

        $allResources = $query->get();
        $this->resourcesHydrator->hydrateResources($allResources);

        $resourcesByParent = $allResources->groupBy('parent_id');

        $roots = $resourcesByParent->get(null, collect())
            ->sortBy('sort_order');

        return $roots->map(function (Resource $resource) use ($resourcesByParent) {
            return $this->buildTree($resource, $resourcesByParent);
        })->values();
    }

    private function buildTree(Resource $resource, Collection $resourcesByParent): ResourceInterface
    {
        $children = $resourcesByParent->get($resource->id, collect())
            ->sortBy('sort_order')
            ->map(function (Resource $child) use ($resourcesByParent) {
                return $this->buildTree($child, $resourcesByParent);
            });

        $resource->setRelation('children', $children);

        return $resource;
    }

    public function getChildren(int $resourceId, ?Closure $modifyQueryUsing = null): Collection
    {
        $resources = Resource::query()
            ->where('parent_id', $resourceId)
            ->when($modifyQueryUsing, fn (Builder $query) => $modifyQueryUsing($query))
            ->orderByDesc('sort_order')
            ->get();

        $this->resourcesHydrator->hydrateResources($resources);
        return $resources;
    }

    public function get(array $resourceIds, ?Closure $modifyQueryUsing = null): Collection
    {
        $resources = Resource::query()
            ->whereIn('id', $resourceIds)
            ->when($modifyQueryUsing, fn (Builder $query) => $modifyQueryUsing($query))
            ->orderByDesc('sort_order')
            ->get();

        $this->resourcesHydrator->hydrateResources($resources);
        return $resources;
    }

    public function getChildrenPaginated(int $resourceId, int $page, int $perPage, ?Closure $modifyQueryUsing = null): LengthAwarePaginator
    {
        $paginator = Resource::query()
            ->where('parent_id', $resourceId)
            ->when($modifyQueryUsing, fn (Builder $query) => $modifyQueryUsing($query))
            ->orderByDesc('sort_order')
            ->paginate($perPage, ['*'], 'page', $page);

        $this->resourcesHydrator->hydrateResources($resources = Collection::make($paginator->getCollection()));
        $paginator->setCollection($resources);
        return $paginator;
    }

    public function findById(int $id): ?ResourceInterface
    {
        $resource = Resource::find($id);

        if (!$resource) {
            return null;
        }

        $this->resourcesHydrator->hydrateResources($resources = Collection::make([$resource]));
        return $resources->first();
    }
}
