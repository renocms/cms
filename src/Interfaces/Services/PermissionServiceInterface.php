<?php

namespace Reno\Cms\Interfaces\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Reno\Cms\Models\Permission;

interface PermissionServiceInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?Permission;

    public function hasPermission(User $user, string $permissionSlug): bool;

    public function syncRegisteredPermissions(): void;
}

