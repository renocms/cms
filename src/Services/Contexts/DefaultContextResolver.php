<?php

namespace Reno\Cms\Services\Contexts;

use Illuminate\Http\Request;
use Reno\Cms\Containers\ContextContainer;
use Reno\Cms\Interfaces\Contexts\ContextResolverInterface;
use Reno\Cms\Interfaces\Repositories\ContextsRepositoryInterface;

class DefaultContextResolver implements ContextResolverInterface
{
    public function __construct(
        private readonly ContextsRepositoryInterface $contextsRepository,
    )
    {
    }

    public function resolve(Request $request): ContextContainer
    {
        $all = $this->contextsRepository->getAll();

        if ($all->isEmpty()) {
            throw new \RuntimeException('No contexts are registered.');
        }

        return $all->first();
    }
}
