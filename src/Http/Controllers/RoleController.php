<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Reno\Cms\DTO\Roles\RoleForCreate;
use Reno\Cms\DTO\Roles\RoleForEdit;
use Reno\Cms\Http\Requests\Roles\RoleStoreRequest;
use Reno\Cms\Http\Requests\Roles\RoleUpdateRequest;
use Reno\Cms\Http\Resources\Roles\RoleResource;
use Reno\Cms\Interfaces\Services\RoleServiceInterface;

class RoleController extends Controller
{
    public function __construct(
        protected RoleServiceInterface $roleService,
    )
    {
    }

    public function index(): JsonResponse
    {
        $roles = $this->roleService->getAll();

        return RoleResource::collection($roles)->response();
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->roleService->findById($id);

        if (!$role) {
            return response()->json(['message' => __('cms::cms.role_not_found')], 404);
        }

        return (new RoleResource($role))->response();
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        $dto = RoleForCreate::createFromRequest($request);

        $role = $this->roleService->create($dto);

        return (new RoleResource($role))->response()->setStatusCode(201);
    }

    public function update(RoleUpdateRequest $request, int $id): JsonResponse
    {
        $dto = RoleForEdit::createFromRequest($request);

        $role = $this->roleService->update($id, $dto);

        return (new RoleResource($role))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->roleService->delete($id);

        return response()->json(['message' => __('cms::cms.role_deleted')], 200);
    }
}

