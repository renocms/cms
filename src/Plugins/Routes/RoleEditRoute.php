<?php

namespace Reno\Cms\Plugins\Routes;

use Reno\Cms\Interfaces\JavascriptRouteInterface;

class RoleEditRoute implements JavascriptRouteInterface
{
    public function getName(): string
    {
        return 'role-edit';
    }

    public function getPath(): string
    {
        return 'roles/:id';
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('components/roles/RoleEdit.vue');
    }

    public function getMeta(): array
    {
        return [];
    }
}
