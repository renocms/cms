<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Reno\Cms\Exceptions\HomeResourceCannotBeDeletedException;
use Reno\Cms\DTO\Resources\ResourceForCreate;
use Reno\Cms\DTO\Resources\ResourceForEdit;
use Reno\Cms\Events\ResourceEditPluginsRegistering;
use Reno\Cms\Http\Requests\Resources\ResourceCreateDraftRequest;
use Reno\Cms\Http\Requests\Resources\ResourceIndexRequest;
use Reno\Cms\Http\Requests\Resources\ResourceMoveRequest;
use Reno\Cms\Http\Requests\Resources\ResourceStoreRequest;
use Reno\Cms\Http\Requests\Resources\ResourceUpdateRequest;
use Reno\Cms\Http\Resources\Resources\JavascriptPluginResource;
use Reno\Cms\Http\Resources\Resources\ResourceEditResource;
use Reno\Cms\Http\Resources\Resources\ResourceTreeResource;
use Reno\Cms\Interfaces\Repositories\ResourceRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\SettingRepositoryInterface;
use Reno\Cms\Interfaces\Services\ResourceServiceInterface;

class ResourceController extends Controller
{
    public function __construct(
        protected ResourceRepositoryInterface $resourceRepository,
        protected ResourceServiceInterface $resourceService,
        protected SettingRepositoryInterface $settingRepository,
    )
    {
    }

    public function index(ResourceIndexRequest $request): JsonResponse
    {
        $contextId = $request->input('context_id');
        $expandedIds = $request->input('ids', []);
        
        // Преобразуем ids в массив целых чисел, если он передан
        if (!empty($expandedIds) && is_array($expandedIds)) {
            $expandedIds = array_map('intval', $expandedIds);
        } else {
            $expandedIds = [];
        }
        
        // Если context_id не указан, возвращаем пустой массив
        if ($contextId === null) {
            return ResourceTreeResource::collection(collect())->response();
        }

        $homeResourceId = $this->settingRepository->getHomeResourceId((int) $contextId);

        ResourceTreeResource::setHomeResourceId($homeResourceId);
        try {
            $tree = $this->resourceRepository->getTreeByContext($contextId, $expandedIds);

            return ResourceTreeResource::collection($tree)->response();
        } finally {
            ResourceTreeResource::setHomeResourceId(null);
        }
    }

    public function makeDraft(ResourceCreateDraftRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $parentId = isset($validated['parent_id']) ? (int) $validated['parent_id'] : null;
        $contextId = isset($validated['context_id']) ? (int) $validated['context_id'] : null;

        $resource = $this->resourceService->makeDraft($parentId, $contextId);

        return (new ResourceEditResource($resource))->response();
    }

    public function store(ResourceStoreRequest $request): JsonResponse
    {
        $dto = ResourceForCreate::createFromRequest($request);

        $resource = $this->resourceService->create($dto);

        return (new ResourceEditResource($resource))->response();
    }

    public function show(int $id): JsonResponse
    {
        try {
            $resource = $this->resourceRepository->findById($id);

            if (!$resource) {
                return response()->json(['message' => __('cms::cms.error_loading_resources')], 404);
            }

            return (new ResourceEditResource($resource))->response();
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(ResourceUpdateRequest $request, int $id): JsonResponse
    {
        $dto = ResourceForEdit::createFromRequest($request);

        $resource = $this->resourceService->update($id, $dto);

        return (new ResourceEditResource($resource))->response();
    }

    public function move(ResourceMoveRequest $request, int $id): JsonResponse
    {
        $parentId = $request->input('parent_id');
        $sortOrder = $request->input('sort_order');

        $resource = $this->resourceService->move($id, $parentId, $sortOrder);

        return (new ResourceEditResource($resource))->response();
    }

    public function children(int $id): JsonResponse
    {
        $children = $this->resourceRepository->getChildren($id);

        $firstChild = $children->first();
        $homeResourceId = $firstChild !== null
            ? $this->settingRepository->getHomeResourceId((int) $firstChild->context_id)
            : null;

        ResourceTreeResource::setHomeResourceId($homeResourceId);
        try {
            return ResourceTreeResource::collection($children)->response();
        } finally {
            ResourceTreeResource::setHomeResourceId(null);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->resourceService->delete($id);
        } catch (HomeResourceCannotBeDeletedException) {
            return response()->json([
                'message' => __('cms::cms.cannot_delete_home_resource'),
            ], 422);
        }

        if (!$result) {
            return response()->json(['message' => __('cms::cms.error_deleting_resource')], 404);
        }

        return response()->json(['message' => __('cms::cms.resource_deleted')]);
    }

    public function getPlugins(): JsonResponse
    {
        $event = new ResourceEditPluginsRegistering();
        Event::dispatch($event);

        $plugins = $event->getAll();

        return JavascriptPluginResource::collection($plugins)->response();
    }
}

