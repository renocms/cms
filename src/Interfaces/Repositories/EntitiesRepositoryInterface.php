<?php

namespace Reno\Cms\Interfaces\Repositories;

use Illuminate\Support\Collection;
use Reno\Cms\Containers\ResourcesCatalogContainer;

interface EntitiesRepositoryInterface
{
    /**
     * @return Collection<ResourcesCatalogContainer>
     */
    public function getAll(): Collection;

    public function findById(int $id): ResourcesCatalogContainer;

    public function findByClassname(string $classname): ResourcesCatalogContainer;

    public function getIdByClassName(string $className): int;

    public function clearCache(): void;
}
