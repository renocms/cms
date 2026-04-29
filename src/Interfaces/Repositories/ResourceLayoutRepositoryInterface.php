<?php

namespace Reno\Cms\Interfaces\Repositories;

use Illuminate\Support\Collection;
use Reno\Cms\Containers\ResourceLayoutContainer;

interface ResourceLayoutRepositoryInterface
{
    /**
     * @return Collection<ResourceLayoutContainer>
     */
    public function getAll(): Collection;

    public function findById(int $id): ResourceLayoutContainer;

    public function findByClassname(string $classname): ResourceLayoutContainer;

    public function getIdByClassName(string $className): int;

    public function getDefaultForResourceType(int $resourceTypeId): ?ResourceLayoutContainer;

    public function clearCache(): void;
}
