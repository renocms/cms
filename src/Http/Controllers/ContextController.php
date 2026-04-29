<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Reno\Cms\Http\Resources\Contexts\ContextContainerResource;
use Reno\Cms\Interfaces\Repositories\ContextsRepositoryInterface;

class ContextController extends Controller
{
    public function __construct(
        protected ContextsRepositoryInterface $contextService,
    )
    {
    }

    public function index(): JsonResponse
    {
        $contexts = $this->contextService->getAll();

        return response()->json([
            'data' => ContextContainerResource::collection($contexts)->resolve(),
        ]);
    }
}
