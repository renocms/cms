<?php

namespace Reno\Cms\Dashboard;

use App\Models\User;
use Reno\Cms\Interfaces\DashboardBlockInterface;

class NewUsersBlock implements DashboardBlockInterface
{
    private const ITEMS_LIMIT = 5;

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/dashboard/NewUsers.vue');
    }

    public function getData(): array
    {
        $items = User::query()
            ->orderByDesc('created_at')
            ->limit(self::ITEMS_LIMIT)
            ->get(['id', 'name', 'created_at'])
            ->map(function (User $user): array {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'created_at' => $user->created_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();

        return [
            'items' => $items,
        ];
    }

    public function getSortOrder(): int
    {
        return 1000;
    }

    public function isFullWidth(): bool
    {
        return false;
    }
}
