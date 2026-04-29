<?php

namespace Reno\Cms\Plugins\Routes;

use Reno\Cms\Interfaces\JavascriptRouteInterface;

class UserEditRoute implements JavascriptRouteInterface
{
    public function getName(): string
    {
        return 'user-edit';
    }

    public function getPath(): string
    {
        return 'users/:id';
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('components/users/UserEdit.vue');
    }

    public function getMeta(): array
    {
        return [];
    }
}
