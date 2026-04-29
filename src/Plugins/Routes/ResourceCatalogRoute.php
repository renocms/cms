<?php

namespace Reno\Cms\Plugins\Routes;

use Reno\Cms\Interfaces\JavascriptRouteInterface;

class ResourceCatalogRoute implements JavascriptRouteInterface
{
    public function getName(): string
    {
        return 'resource-catalog';
    }

    public function getPath(): string
    {
        return 'resources/catalog/:catalogId?';
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('components/resources/ResourceCatalog.vue');
    }

    public function getMeta(): array
    {
        return [];
    }
}
