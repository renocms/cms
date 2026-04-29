<?php

namespace Reno\Cms\Interfaces\Services;

interface ResourceResolverInterface
{
    public function resolveResourceIdByPath(int $contextId, string $path): ?int;
}
