<?php

namespace Reno\Cms\Dashboard;

use Reno\Cms\Interfaces\DashboardBlockInterface;

class WelcomeBlock implements DashboardBlockInterface
{
    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/dashboard/Welcome.vue');
    }

    public function getData(): array
    {
        return [];
    }

    public function getSortOrder(): int
    {
        return 0;
    }

    public function isFullWidth(): bool
    {
        return true;
    }
}
