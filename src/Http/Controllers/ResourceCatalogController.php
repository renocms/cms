<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Reno\Cms\Columns\AbstractColumn;
use Reno\Cms\Http\Resources\Resources\ResourceCatalogRowsCollection;
use Reno\Cms\Http\Requests\Resources\ResourceCatalogResourcesRequest;
use Reno\Cms\Http\Requests\Resources\ResourceCatalogShowRequest;
use Reno\Cms\Http\Resources\Resources\ResourceCatalogResource;
use Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\ResourceRepositoryInterface;
use Reno\Cms\Interfaces\Resources\ResourcesCatalogInterface;
use Reno\Cms\Models\Resource;

class ResourceCatalogController extends Controller
{
    public function __construct(
        protected ResourceRepositoryInterface $resourceRepository,
        protected ResourceLayoutRepositoryInterface $resourceLayoutRepository,
    )
    {
    }

    public function show(ResourceCatalogShowRequest $request): JsonResponse
    {
        $catalogRoot = $this->resourceRepository->findById($request->integer('catalog_id'));
        $currentResource = $this->resourceRepository->findById($request->integer('resource_id'));

        if (!$catalogRoot instanceof Resource || !$currentResource instanceof Resource) {
            return response()->json(['message' => __('cms::cms.error_loading_resource')], 404);
        }

        try {
            ['catalog' => $catalog, 'layout_container' => $layoutContainer] = $this->resolveCatalogData($catalogRoot);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        try {
            $this->assertCurrentResourceBelongsToCatalogTree($catalogRoot, $currentResource);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return (new ResourceCatalogResource([
            'resource' => $currentResource,
            'catalog' => $catalog,
            'layout_container' => $layoutContainer,
            'catalog_root' => $catalogRoot,
        ]))->response();
    }

    public function resources(ResourceCatalogResourcesRequest $request): JsonResponse
    {
        $catalogRoot = $this->resourceRepository->findById($request->integer('catalog_id'));
        $currentResource = $this->resourceRepository->findById($request->integer('resource_id'));

        if (!$catalogRoot instanceof Resource || !$currentResource instanceof Resource) {
            return response()->json(['message' => __('cms::cms.error_loading_resource')], 404);
        }

        try {
            ['catalog' => $catalog] = $this->resolveCatalogData($catalogRoot);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        try {
            $this->assertCurrentResourceBelongsToCatalogTree($catalogRoot, $currentResource);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $paginator = $this->resourceRepository->getChildrenPaginated(
            $currentResource->id,
            $request->integer('page', 1),
            $request->integer('per_page', 20),
            function (Builder $query) use ($catalog): void {
                $catalog->modifyQueryUsing($query);
            },
        );

        return (new ResourceCatalogRowsCollection(
            $paginator,
            $catalog->getCatalogSchema(),
        ))->response();
    }

    private function resolveCatalogData(Resource $resource): array
    {
        if (!$resource->resource_layout_id) {
            throw new \RuntimeException(__('cms::cms.error_loading_resource'));
        }

        $layoutContainer = $this->resourceLayoutRepository->findById($resource->resource_layout_id);
        $catalog = $layoutContainer->getResourceCatalog();

        if (!$catalog instanceof ResourcesCatalogInterface) {
            throw new \RuntimeException(__('cms::cms.error_loading_resource'));
        }

        $schema = $catalog->getCatalogSchema();

        foreach ($schema as $column) {
            if (!$column instanceof AbstractColumn) {
                throw new \RuntimeException('Unsupported catalog column: ' . get_debug_type($column));
            }
        }

        return [
            'catalog' => $catalog,
            'layout_container' => $layoutContainer,
        ];
    }

    private function assertCurrentResourceBelongsToCatalogTree(Resource $catalogRoot, Resource $currentResource): void
    {
        if ($currentResource->id === $catalogRoot->id) {
            return;
        }

        $parentIds = $currentResource->getParentIds();

        if (!in_array($catalogRoot->id, $parentIds, true)) {
            throw new \RuntimeException(__('cms::cms.error_catalog_resource_not_in_tree'));
        }
    }
}
