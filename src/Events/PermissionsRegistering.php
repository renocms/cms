<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermissionsRegistering
{
    use Dispatchable, SerializesModels;

    /**
     * @var array<string, array{slug: string, group: string|null}>
     */
    protected array $permissions = [];

    public function addPermission(string $slug, ?string $group = null): void
    {
        $this->permissions[$slug] = [
            'slug' => $slug,
            'group' => $group,
        ];
    }

    /**
     * @return array<array{slug: string, group: string|null}>
     */
    public function getPermissions(): array
    {
        return array_values($this->permissions);
    }
}
