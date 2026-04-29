<?php

namespace Reno\Cms\Plugins\Routes;

use Reno\Cms\Interfaces\JavascriptRouteInterface;

class ResourceEditRoute implements JavascriptRouteInterface
{
    public function getName(): string
    {
        return 'resource-edit';
    }

    public function getPath(): string
    {
        return 'resources/:id';
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('components/resources/ResourceEditWrapper.vue');
    }

    public function getMeta(): array
    {
        return [];
    }
}
