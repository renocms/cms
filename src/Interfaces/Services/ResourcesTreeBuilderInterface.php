<?php

namespace Reno\Cms\Interfaces\Services;

use Illuminate\Database\Eloquent\Collection;
use Reno\Cms\DTO\Resources\ResourceTreeBuilderParams;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ResourcesTreeBuilderInterface
{
    public function getTree(ResourceTreeBuilderParams $params): Collection;

    public function getList(ResourceTreeBuilderParams $params): Collection;

    public function getPaginatedList(ResourceTreeBuilderParams $params, int $page = 1, int $perPage = 12, string $pageName = 'page'): LengthAwarePaginator;
}
