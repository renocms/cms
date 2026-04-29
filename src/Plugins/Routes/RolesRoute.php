<?php

namespace Reno\Cms\Plugins\Routes;

use Reno\Cms\Interfaces\JavascriptRouteInterface;

class RolesRoute implements JavascriptRouteInterface
{
    public function getName(): string
    {
        return 'roles';
    }

    public function getPath(): string
    {
        return 'roles';
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('components/roles/Roles.vue');
    }

    public function getMeta(): array
    {
        return [];
    }
}
