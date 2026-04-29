<?php

namespace Reno\Cms\Interfaces\Services;

use Illuminate\Support\Collection;
use Reno\Cms\DTO\Roles\RoleForCreate;
use Reno\Cms\DTO\Roles\RoleForEdit;
use Reno\Cms\Models\Role;

interface RoleServiceInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?Role;

    public function create(RoleForCreate $dto): Role;

    public function update(int $id, RoleForEdit $dto): Role;

    public function delete(int $id): bool;
}

