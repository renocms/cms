<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Reno\Cms\Http\Resources\Roles\PermissionResource;
use Reno\Cms\Interfaces\Services\PermissionServiceInterface;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionServiceInterface $permissionService,
    )
    {
    }

    public function index(): JsonResponse
    {
        $permissions = $this->permissionService->getAll();
        $resources = PermissionResource::collection($permissions);

        return response()->json([
            'data' => $resources->collection->groupBy('group'),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $permission = $this->permissionService->findById($id);

        if (!$permission) {
            return response()->json(['message' => __('cms::cms.permission_not_found')], 404);
        }

        return (new PermissionResource($permission))->response();
    }
}

