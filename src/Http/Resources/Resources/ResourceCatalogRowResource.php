<?php

namespace Reno\Cms\Http\Resources\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Catalogs\Columns\AbstractColumn;
use Reno\Cms\Catalogs\Columns\ImageColumn;
use Reno\Cms\Catalogs\Columns\TextColumn;
use Reno\Cms\Containers\ResourceLayoutContainer;
use Reno\Cms\Http\Resources\Media\MediaResource;
use Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\SettingRepositoryInterface;
use Reno\Cms\Models\Resource;

class ResourceCatalogRowResource extends JsonResource
{
    public function __construct(
        $resource,
        private readonly array $columns,
    )
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        /** @var Resource $resource */
        $resource = $this->resource;

        $cells = [];

        /** @var AbstractColumn $column */
        foreach ($this->columns as $column) {
            $cells[$column->getKey()] = $this->makeCell($resource, $column);
        }

        $layoutMeta = $this->formatLayout($resource->resource_layout_id);

        return [
            'id' => $resource->id,
            'title' => $resource->getTitle() ?? $resource->slug,
            'slug' => $resource->slug,
            'status' => $resource->status,
            'is_home' => $this->isHomeResource($resource),
            'is_folder' => (bool) $resource->is_folder,
            'allow_children' => $layoutMeta['allow_children'],
            'children_layouts' => $layoutMeta['children_layouts'],
            'cells' => $cells,
        ];
    }

    private function formatLayout(?int $resourceLayoutId): array
    {
        if (!$resourceLayoutId) {
            return [
                'allow_children' => true,
                'children_layouts' => null,
            ];
        }

        /** @var ResourceLayoutRepositoryInterface $layoutsRepository */
        $layoutsRepository = resolve(ResourceLayoutRepositoryInterface::class);

        try {
            $layoutContainer = $layoutsRepository->findById($resourceLayoutId);
        } catch (\RuntimeException) {
            return [
                'allow_children' => false,
                'children_layouts' => [],
            ];
        }

        return [
            'allow_children' => $layoutContainer->getLayout()->allowChildren(),
            'children_layouts' => $layoutContainer->getChildrenLayouts()
                ?->map(fn (ResourceLayoutContainer $child) => ResourceLayoutSimpleResource::make($child)->resolve())
                ->toArray(),
        ];
    }

    private function isHomeResource(Resource $resource): bool
    {
        /** @var SettingRepositoryInterface $settingRepository */
        $settingRepository = resolve(SettingRepositoryInterface::class);

        return $settingRepository->getHomeResourceId((int) $resource->context_id) === (int) $resource->id;
    }

    private function makeCell(Resource $resource, AbstractColumn $column): array
    {
        return match (true) {
            $column instanceof TextColumn => [
                'type' => $column->getType(),
                'value' => $column->resolveValue($resource),
            ],
            $column instanceof ImageColumn => $this->makeImageCell($resource, $column),
            default => [
                'type' => $column->getType(),
                'value' => null,
            ],
        };
    }

    private function makeImageCell(Resource $resource, ImageColumn $column): array
    {
        $media = $column->resolveMedia($resource);

        return [
            'type' => $column->getType(),
            'media' => $media ? MediaResource::make($media)->resolve() : null,
        ];
    }
}
