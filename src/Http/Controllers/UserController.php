<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Reno\Cms\DTO\Users\UserForCreate;
use Reno\Cms\DTO\Users\UserForEdit;
use Reno\Cms\Http\Requests\Users\UserStoreRequest;
use Reno\Cms\Http\Requests\Users\UserUpdateRequest;
use Reno\Cms\Http\Resources\Users\UserResource;
use Reno\Cms\Interfaces\Services\UserServiceInterface;

class UserController extends Controller
{
    public function __construct(
        protected UserServiceInterface $userService,
    )
    {
    }

    public function index(): JsonResponse
    {
        $users = $this->userService->getAll();

        return UserResource::collection($users)->response();
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user) {
            return response()->json(['message' => __('cms::cms.user_not_found')], 404);
        }

        return (new UserResource($user))->response();
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $dto = UserForCreate::createFromRequest($request);

        $user = $this->userService->create($dto);

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function update(UserUpdateRequest $request, int $id): JsonResponse
    {
        $dto = UserForEdit::createFromRequest($request);

        $user = $this->userService->update($id, $dto);

        return (new UserResource($user))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->userService->delete($id);

        return response()->json(['message' => __('cms::cms.user_deleted')], 200);
    }
}

