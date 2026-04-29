<?php

namespace Reno\Cms\Blocks\Dashboard;

use App\Models\User;
use Reno\Cms\Interfaces\DashboardBlockInterface;

class UsersCountBlock implements DashboardBlockInterface
{
    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/dashboard/UsersCount.vue');
    }

    public function getData(): array
    {
        return [
            'count' => User::count(),
        ];
    }

    public function getSortOrder(): int
    {
        return 20;
    }
}

