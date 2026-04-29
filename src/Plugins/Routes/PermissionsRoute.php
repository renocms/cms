<?php

namespace Reno\Cms\Plugins\Routes;

use Reno\Cms\Interfaces\JavascriptRouteInterface;

class PermissionsRoute implements JavascriptRouteInterface
{
    public function getName(): string
    {
        return 'permissions';
    }

    public function getPath(): string
    {
        return 'permissions';
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('components/permissions/Permissions.vue');
    }

    public function getMeta(): array
    {
        return [];
    }
}
