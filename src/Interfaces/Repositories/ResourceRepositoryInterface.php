<?php

namespace Reno\Cms\Interfaces\Repositories;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Reno\Cms\Interfaces\Resources\ResourceInterface;

interface ResourceRepositoryInterface
{
    /**
     * Получить дерево ресурсов для контекста
     *
     * @return Collection<ResourceInterface>
     */
    public function getTreeByContext(int $contextId, array $expandedIds = []): Collection;

    /**
     * Получить дочерние ресурсы конкретного ресурса
     *
     * @return Collection<ResourceInterface>
     */
    public function getChildren(int $resourceId, ?Closure $modifyQueryUsing = null): Collection;

    /**
     * Получить дочерние ресурсы конкретного ресурса с пагинацией
     *
     * @param Closure(Builder): void|null $modifyQueryUsing
     */
    public function getChildrenPaginated(int $resourceId, int $page, int $perPage, ?Closure $modifyQueryUsing = null): LengthAwarePaginator;

    /**
     * Найти ресурс по ID
     */
    public function findById(int $id): ?ResourceInterface;

    public function get(array $resourceIds, ?Closure $modifyQueryUsing = null): Collection;
}

