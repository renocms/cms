<?php

namespace Reno\Cms\Services\Resources;

use Reno\Cms\Interfaces\Services\ResourceVersionServiceInterface;
use Reno\Cms\Models\Resource;
use Reno\Cms\Models\ResourceVersion;

class ResourceVersionService implements ResourceVersionServiceInterface
{
    public function create(int $resourceId): void
    {
        $resource = Resource::with(['resourceValues.resourceField'])->find($resourceId);
        if (!$resource) {
            return;
        }

        // Формируем снимок данных ресурса
        $data = [
            'context_id' => $resource->context_id,
            'resource_type_id' => $resource->resource_type_id,
            'resource_layout_id' => $resource->resource_layout_id,
            'parent_id' => $resource->parent_id,
            'slug' => $resource->slug,
            'status' => $resource->status,
            'sort_order' => $resource->sort_order,
            'show_in_menu' => $resource->show_in_menu,
            'published_at' => $resource->published_at?->toDateTimeString(),
            'author_id' => $resource->author_id,
            'editor_id' => $resource->editor_id,
        ];

        // Формируем массив значений
        $values = [];
        foreach ($resource->resourceValues as $resourceValue) {
            $values[] = [
                'id' => $resourceValue->id,
                'resource_field_id' => $resourceValue->resource_field_id,
                'value' => $resourceValue->value,
                'media_id' => $resourceValue->media_id,
            ];
        }

        ResourceVersion::create([
            'resource_id' => $resourceId,
            'data' => $data,
            'values' => $values,
            'created_by' => auth()->id(),
        ]);
    }
}
