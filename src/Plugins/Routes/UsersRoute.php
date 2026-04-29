<?php

namespace Reno\Cms\Plugins\Routes;

use Reno\Cms\Interfaces\JavascriptRouteInterface;

class UsersRoute implements JavascriptRouteInterface
{
    public function getName(): string
    {
        return 'users';
    }

    public function getPath(): string
    {
        return 'users';
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('components/users/Users.vue');
    }

    public function getMeta(): array
    {
        return [];
    }
}
