<?php

namespace Reno\Cms\Http\Actions;

use Illuminate\Support\Facades\View;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Reno\Cms\Containers\ContextContainer;
use Reno\Cms\Interfaces\Resources\ResourceInterface;
use Reno\Cms\Interfaces\Contexts\ContextResolverInterface;
use Reno\Cms\Interfaces\Repositories\ResourceRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface;
use Reno\Cms\Interfaces\Services\ResourceResolverInterface;
use Reno\Cms\Models\Resource;

class ShowTreeResourceAction
{
    public function __construct(
        private readonly ContextResolverInterface $contextResolver,
        private readonly ResourceResolverInterface $resourceResolver,
        private readonly ResourceRepositoryInterface $resourceRepository,
        private readonly ResourceLayoutRepositoryInterface $resourceLayoutRepository,
    )
    {
    }

    public function __invoke(Request $request, string $path = ''): Htmlable
    {
        $context = $this->getOrResolveContext($request);
        $contextId = $context->getId();
        $pathFromRequest = $request->attributes->get('path', $path);
        $pathFromRequest = is_string($pathFromRequest) ? $pathFromRequest : '';
        $resourceId = $this->resourceResolver->resolveResourceIdByPath($contextId, $pathFromRequest);

        if (!is_int($resourceId) || $resourceId <= 0) {
            abort(404);
        }

        $resource = $this->resourceRepository->findById($resourceId);

        if (!$resource instanceof Resource) {
            abort(404);
        }

        if ($resource->status !== 'published' && !$this->hasAnyRole($request)) {
            abort(404);
        }

        if ($resource->resource_layout_id === null) {
            abort(404);
        }

        $layoutContainer = $this->resourceLayoutRepository->findById($resource->resource_layout_id);

        app()->bind(Resource::class, fn () => $resource);
        app()->bind(ResourceInterface::class, fn () => $resource);
        View::share('resource', $resource);

        $view = View::make($layoutContainer->getLayout()->getViewName());

        if ($composer = $layoutContainer->getViewComposer()) {
            $composer->compose($view, $resource);
        }

        return $view;
    }

    private function getOrResolveContext(Request $request): ContextContainer
    {
        $contextContainer = $request->attributes->get('cms_context');

        if ($contextContainer instanceof ContextContainer) {
            return $contextContainer;
        }

        return $this->contextResolver->resolve($request);
    }

    private function hasAnyRole(Request $request): bool
    {
        $user = $request->user();

        if ($user === null) {
            return false;
        }

        return $user->roles()->exists();
    }
}
