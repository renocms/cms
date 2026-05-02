<?php

namespace Reno\Cms\Services\Resources;

use Reno\Cms\Containers\ContextContainer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Reno\Cms\DTO\Resources\ResourceSearchCriteria;
use Reno\Cms\DTO\Resources\ResourceTreeBuilderParams;
use Reno\Cms\DTO\Resources\Sort;
use Reno\Cms\DTO\Resources\ValueFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Reno\Cms\Exceptions\CurrentContextNotResolvedException;
use Reno\Cms\Exceptions\InvalidSortRuleException;
use Reno\Cms\Exceptions\InvalidValueFilterException;
use Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface;
use Reno\Cms\Interfaces\Services\ResourceSearchEngineInterface;
use Reno\Cms\Interfaces\Services\ResourcesTreeBuilderInterface;
use Reno\Cms\Models\Resource;
use Reno\Cms\Models\ResourceField;
use Reno\Cms\Models\ResourceValue;

class ResourcesTreeBuilder implements ResourcesTreeBuilderInterface
{
    public function __construct(
        private readonly ResourceLayoutRepositoryInterface $resourceLayoutRepository,
        private readonly ResourcesHydrator $resourcesHydrator,
        private readonly ResourceSearchEngineInterface $resourceSearchEngine,
    )
    {
    }

    public function getTree(ResourceTreeBuilderParams $params): Collection
    {
        $contextId = $this->resolveContextId($params->contextId, $params->parentId);
        $depth = max(1, $params->depth);

        $roots = $this->loadChildren($contextId, $params->parentId, $params);

        $this->attachChildrenByLevels($roots, $depth, $contextId, $params);

        return $roots;
    }

    public function getList(ResourceTreeBuilderParams $params): Collection
    {
        $contextId = $this->resolveContextId($params->contextId, $params->parentId);
        $depth = max(0, $params->depth);
        $searchSubquery = $this->makeSearchSubquery(
            $params,
            $contextId,
        );

        $parentIds = $params->parentId ? [$params->parentId] : [];
        $parentIdsForChildren = null;

        for ($level = 1; $level <= $depth; ++$level) {
            $folders = $this->loadFolderLevels($contextId, $level === 1 ? $params->parentId : $parentIdsForChildren, $params);

            $parentIdsForChildren = $folders
                ->filter(fn (Resource $resource) => $this->shouldExpandFolder($resource, $params))
                ->pluck('id')
                ->toArray();

            if ($parentIdsForChildren === []) {
                break;
            }

            $parentIds = array_merge($parentIds, $parentIdsForChildren);
        }

        if (empty($parentIds)) {
            return Collection::make();
        }

        return $this->loadChildren($contextId, $parentIds, $params, $searchSubquery);
    }

    public function getPaginatedList(ResourceTreeBuilderParams $params, int $page = 1, int $perPage = 12, string $pageName = 'page'): LengthAwarePaginator
    {
        $contextId = $this->resolveContextId($params->contextId, $params->parentId);
        $depth = max(0, $params->depth);
        $searchSubquery = $this->makeSearchSubquery(
            $params,
            $contextId,
        );

        $parentIds = $params->parentId ? [$params->parentId] : [];
        $parentIdsForChildren = null;

        for ($level = 1; $level <= $depth; ++$level) {
            $folders = $this->loadFolderLevels($contextId, $level === 1 ? $params->parentId : $parentIdsForChildren, $params);

            $parentIdsForChildren = $folders
                ->filter(fn (Resource $resource) => $this->shouldExpandFolder($resource, $params))
                ->pluck('id')
                ->toArray();

            if ($parentIdsForChildren === []) {
                break;
            }

            $parentIds = array_merge($parentIds, $parentIdsForChildren);
        }

        $paginator = $this->makeChildrenQuery($contextId, $parentIds, $params, $searchSubquery)
            ->paginate($perPage, pageName: $pageName, page: $page);

        $resources = Collection::make($paginator->getCollection());
        $this->resourcesHydrator->hydrateResources($resources, $params->onlyFields);
        $paginator->setCollection($resources);
        return $paginator;
    }

    private function resolveContextId(?int $contextId, ?int $parentId): ?int
    {
        if ($contextId !== null) {
            return $contextId;
        }

        if ($parentId) {
            return $contextId;
        }

        if (!app()->bound('cms.current_context')) {
            throw new CurrentContextNotResolvedException();
        }

        /** @var ContextContainer $container */
        $container = app('cms.current_context');

        return $container->getId();
    }

    private function attachChildrenByLevels(
        Collection $nodes,
        int $levelsRemaining,
        ?int $contextId,
        ResourceTreeBuilderParams $params,
    ): void
    {
        if ($levelsRemaining <= 1) {
            foreach ($nodes as $node) {
                $node->setRelation('children', collect());
            }

            return;
        }

        $expandIds = $nodes
            ->filter(fn (Resource $resource) => $this->shouldExpandFolder($resource, $params))
            ->pluck('id')
            ->values()
            ->all();

        if ($expandIds === []) {
            foreach ($nodes as $node) {
                $node->setRelation('children', collect());
            }

            return;
        }

        $allChildren = $this->loadChildren($contextId, $expandIds, $params);
        $groupedByParent = $allChildren->groupBy('parent_id');

        foreach ($nodes as $node) {
            if (!in_array($node->id, $expandIds, true)) {
                $node->setRelation('children', collect());

                continue;
            }

            $children = $groupedByParent->get($node->id, collect())->values();
            $node->setRelation('children', $children);
        }

        if ($levelsRemaining === 2) {
            foreach ($allChildren as $child) {
                $child->setRelation('children', collect());
            }

            return;
        }

        $this->attachChildrenByLevels($allChildren->values(), $levelsRemaining - 1, $contextId, $params);
    }

    private function shouldExpandFolder(Resource $resource, ResourceTreeBuilderParams $params): bool
    {
        return $resource->is_folder
            && ($params->showCatalogChildren || !$this->isLayoutCatalog($resource));
    }

    private function isLayoutCatalog(Resource $resource): bool
    {
        return $resource->resource_layout_id &&
            $this->resourceLayoutRepository->findById($resource->resource_layout_id)->isCatalog();
    }

    private function loadChildren(
        ?int $contextId,
        array|int|null $parentIds,
        ResourceTreeBuilderParams $params,
        ?QueryBuilder $searchSubquery = null,
    ): Collection
    {
        $resources = $this->makeChildrenQuery($contextId, $parentIds, $params, $searchSubquery)->get();
        $this->resourcesHydrator->hydrateResources($resources, $params->onlyFields);
        return $resources;
    }

    protected function makeChildrenQuery(
        ?int $contextId,
        array|int|null $parentIds,
        ResourceTreeBuilderParams $params,
        ?QueryBuilder $searchSubquery = null,
    ): Builder
    {
        if (is_int($parentIds)) {
            $parentIds = [$parentIds];
        }

        $query = Resource::query();

        if ($parentIds === null) {
            $query->whereNull('parent_id');
        } else {
            $query->whereIn('parent_id', $parentIds);
        }

        if ($searchSubquery !== null) {
            $query
                ->select(Resource::getTableName() . '.*')
                ->joinSub($searchSubquery, 'search_hits', function ($join): void {
                    $join->on('search_hits.resource_id', '=', Resource::getTableName() . '.id');
                });
        }

        $this->applyBaseConstraints($query, $contextId, $params);

        if ($searchSubquery !== null) {
            $this->applySearchSort($query);
        } else {
            $this->applySort($query, $params);
        }

        if ($params->limit) {
            $query->limit($params->limit);
        }

        return $query;
    }

    private function loadFolderLevels(?int $contextId, array|int|null $parentIds, ResourceTreeBuilderParams $params): Collection
    {
        if (is_int($parentIds)) {
            $parentIds = [$parentIds];
        }

        $query = Resource::query()
            ->where('is_folder', true);

        if ($contextId) {
            $query->where('context_id', $contextId);
        }

        if ($parentIds === null) {
            $query->whereNull('parent_id');
        } else {
            $query->whereIn('parent_id', $parentIds);
        }

        if ($params->onlyPublished) {
            $query->where('status', 'published');
        }

        if ($params->modifyFoldersQueryUsing !== null) {
            ($params->modifyFoldersQueryUsing)($query);
        }

        return $query->get();
    }

    private function applyBaseConstraints(Builder $query, ?int $contextId, ResourceTreeBuilderParams $params): void
    {
        if ($contextId) {
            $query->where('context_id', $contextId);
        }

        if ($params->onlyPublished) {
            $query->where('status', 'published');
        }

        if ($params->onlyForMenu) {
            $query->where('show_in_menu', true);
        }

        if ($params->onlyLayouts !== null) {
            $query->whereIn('resource_layout_id', array_map(function (string $layoutClass) {
                return $this->resourceLayoutRepository->findByClassname($layoutClass)->getId();
            }, $params->onlyLayouts));
        }

        if ($params->exceptLayouts !== null) {
            foreach ($params->exceptLayouts as $layoutClass) {
                $query->where('resource_layout_id', '!=', $this->resourceLayoutRepository->findByClassname($layoutClass)->getId());
            }
        }

        if ($params->valueFilters !== null) {
            foreach ($params->valueFilters as $valueFilter) {
                if (!$valueFilter instanceof ValueFilter) {
                    throw new InvalidValueFilterException(
                        'The valueFilters parameter must contain only ' . ValueFilter::class . ' instances',
                    );
                }

                $valueFilter->addToQuery($query, Resource::getTableName());
            }
        }

        if ($params->modifyQueryUsing !== null) {
            ($params->modifyQueryUsing)($query);
        }
    }

    private function applySort(Builder $query, ResourceTreeBuilderParams $params): void
    {
        $resourceTable = Resource::getTableName();

        if ($params->sortBy === null || $params->sortBy === []) {
            $this->addDefaultSort($query, $resourceTable);
            return;
        }

        foreach ($params->sortBy as $sortByValue) {
            if (!$sortByValue instanceof Sort) {
                throw new InvalidSortRuleException(
                    'The sortByValues parameter must contain only ' . Sort::class . ' instances',
                );
            }

            if ($sortByValue->isResource()) {
                $query->orderBy($resourceTable . '.' . $sortByValue->getField(), $sortByValue->getDirection());
                continue;
            }

            if (!$sortByValue->isValue()) {
                throw new InvalidSortRuleException('Unknown sort rule type');
            }

            $query->orderBy(
                $this->makeResourceValueSortSubquery($resourceTable, $sortByValue->getField()),
                $sortByValue->getDirection(),
            );
        }

        $this->addDefaultSort($query, $resourceTable);
    }

    private function applySearchSort(Builder $query): void
    {
        $query->orderByDesc('search_hits.score');
        $this->addDefaultSort($query, Resource::getTableName());
    }

    private function makeSearchSubquery(ResourceTreeBuilderParams $params, ?int $contextId): ?QueryBuilder
    {
        $searchQuery = trim((string) $params->searchQuery);
        if ($searchQuery === '') {
            return null;
        }

        $criteria = new ResourceSearchCriteria(
            searchQuery: $searchQuery,
            contextId: $contextId,
        );

        return $this->resourceSearchEngine->makeSearchSubquery($criteria);
    }

    private function makeResourceValueSortSubquery(string $resourceTable, string $field): Builder
    {
        $resourceValuesTable = ResourceValue::getTableName();
        $resourceFieldsTable = ResourceField::getTableName();
        $resourceFieldsAlias = 'sort_resource_fields';

        return ResourceValue::query()
            ->select($resourceValuesTable . '.value')
            ->join(
                $resourceFieldsTable . ' as ' . $resourceFieldsAlias,
                $resourceFieldsAlias . '.id',
                '=',
                $resourceValuesTable . '.resource_field_id',
            )
            ->whereColumn($resourceValuesTable . '.resource_id', $resourceTable . '.id')
            ->where($resourceFieldsAlias . '.key', $field)
            ->limit(1);
    }

    private function addDefaultSort(Builder $query, string $resourceTable): void
    {
        $query
            ->orderBy($resourceTable . '.sort_order')
            ->orderBy($resourceTable . '.id');
    }
}
