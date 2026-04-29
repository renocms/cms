<?php

namespace Reno\Cms\Interfaces\Repositories;

use Illuminate\Support\Collection;
use Reno\Cms\Containers\ContextContainer;

interface ContextsRepositoryInterface
{
    /**
     * @return Collection<int, ContextContainer>
     */
    public function getAll(): Collection;

    public function findById(int $id): ContextContainer;

    public function findByClassName(string $className): ContextContainer;

    public function getIdByClassName(string $className): int;

    public function clearCache(): void;
}
