<?php

namespace Reno\Cms\Interfaces\Repositories;

use Illuminate\Support\Collection;
use Reno\Cms\Containers\ResourceTypeContainer;

interface ResourceTypeRepositoryInterface
{
    /**
     * @return Collection<ResourceTypeContainer>
     */
    public function getAll(): Collection;

    public function findById(int $id): ResourceTypeContainer;

    public function findByClassname(string $classname): ResourceTypeContainer;

    public function getIdByClassName(string $className): int;

    public function clearCache(): void;
}
