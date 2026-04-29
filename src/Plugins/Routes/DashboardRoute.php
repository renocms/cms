<?php

namespace Reno\Cms\Plugins\Routes;

use Reno\Cms\Interfaces\JavascriptRouteInterface;

class DashboardRoute implements JavascriptRouteInterface
{
    public function getName(): string
    {
        return 'dashboard';
    }

    public function getPath(): string
    {
        return '';
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('components/common/Dashboard.vue');
    }

    public function getMeta(): array
    {
        return [];
    }
}
