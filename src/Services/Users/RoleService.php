<?php

namespace Reno\Cms\Services\Users;

use Illuminate\Support\Collection;
use Reno\Cms\DTO\Roles\RoleForCreate;
use Reno\Cms\DTO\Roles\RoleForEdit;
use Reno\Cms\Interfaces\Services\RoleServiceInterface;
use Reno\Cms\Models\Role;

class RoleService implements RoleServiceInterface
{
    public function getAll(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function findById(int $id): ?Role
    {
        return Role::with('permissions')->find($id);
    }

    public function create(RoleForCreate $dto): Role
    {
        $role = Role::create([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'description' => $dto->description,
        ]);

        if (!empty($dto->permissions)) {
            $role->permissions()->sync($dto->permissions);
        }

        $role->load('permissions');

        return $role;
    }

    public function update(int $id, RoleForEdit $dto): Role
    {
        $role = Role::findOrFail($id);

        $role->update([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'description' => $dto->description,
        ]);

        $role->permissions()->sync($dto->permissions);

        $role->load('permissions');

        return $role;
    }

    public function delete(int $id): bool
    {
        $role = Role::findOrFail($id);
        return $role->delete();
    }
}
