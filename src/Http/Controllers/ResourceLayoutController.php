<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Reno\Cms\Http\Resources\Resources\ResourceLayoutResource;
use Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface;

class ResourceLayoutController extends Controller
{
    public function __construct(
        protected ResourceLayoutRepositoryInterface $resourceLayoutRepository,
    )
    {
    }

    public function index(): JsonResponse
    {
        $layouts = $this->resourceLayoutRepository->getAll();

        return ResourceLayoutResource::collection($layouts->values())->response();
    }
}

