<?php

namespace Reno\Cms\Dashboard;

use Reno\Cms\Interfaces\DashboardBlockInterface;
use Reno\Cms\Models\Resource;

class RecentResourceChangesBlock implements DashboardBlockInterface
{
    private const ITEMS_LIMIT = 5;

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/dashboard/RecentResourceChanges.vue');
    }

    public function getData(): array
    {
        $items = Resource::query()
            ->with([
                'resourceValues',
                'resourceValues.resourceField',
                'resourceValues.media',
                'author:id,name',
                'editor:id,name',
            ])
            ->orderByDesc('updated_at')
            ->limit(self::ITEMS_LIMIT)
            ->get()
            ->map(function (Resource $resource): array {
                return [
                    'id' => $resource->id,
                    'title' => $resource->getTitle() ?? __('cms.no_title'),
                    'updated_at' => $resource->updated_at?->toIso8601String(),
                    'editor_name' => $resource->editor?->name ?? $resource->author?->name,
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
        return 999;
    }

    public function isFullWidth(): bool
    {
        return false;
    }
}
