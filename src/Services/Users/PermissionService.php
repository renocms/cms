<?php

namespace Reno\Cms\Services\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Reno\Cms\Events\PermissionsRegistering;
use Reno\Cms\Interfaces\Services\PermissionServiceInterface;
use Reno\Cms\Models\Permission;

class PermissionService implements PermissionServiceInterface
{
    protected bool $permissionsSynchronized = false;

    public function getAll(): Collection
    {
        $this->syncRegisteredPermissions();

        return Permission::all();
    }

    public function findById(int $id): ?Permission
    {
        $this->syncRegisteredPermissions();

        return Permission::with('roles')->find($id);
    }

    public function hasPermission(User $user, string $permissionSlug): bool
    {
        $this->syncRegisteredPermissions();

        return $user->roles()
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();
    }

    public function syncRegisteredPermissions(): void
    {
        if ($this->permissionsSynchronized || !$this->isAdminRequest()) {
            return;
        }

        $event = new PermissionsRegistering();
        Event::dispatch($event);

        $existingPermissions = Permission::query()
            ->pluck('slug')
            ->flip()
            ->all();

        foreach ($event->getPermissions() as $permission) {
            if (isset($existingPermissions[$permission['slug']])) {
                continue;
            }

            Permission::query()->create([
                'slug' => $permission['slug'],
                'group' => $permission['group'],
            ]);

            $existingPermissions[$permission['slug']] = true;
        }

        $this->permissionsSynchronized = true;
    }

    private function isAdminRequest(): bool
    {
        if (app()->runningInConsole() || !app()->bound('request')) {
            return false;
        }

        /** @var Request $request */
        $request = app('request');
        $prefix = trim((string) config('cms.admin_prefix', 'admin'), '/');
        $path = trim($request->path(), '/');

        return $path === $prefix || str_starts_with($path, $prefix . '/');
    }
}
