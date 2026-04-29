<?php

namespace Reno\Cms\Blocks\Dashboard;

use Reno\Cms\Interfaces\DashboardBlockInterface;
use Reno\Cms\Models\Resource;

class ResourcesCountBlock implements DashboardBlockInterface
{
    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/dashboard/ResourcesCount.vue');
    }

    public function getData(): array
    {
        return [
            'count' => Resource::count(),
        ];
    }

    public function getSortOrder(): int
    {
        return 10;
    }
}

