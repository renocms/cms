<?php

namespace Reno\Cms\Http\Resources\Resources;

use Illuminate\Http\Request;
use Reno\Cms\Models\Resource;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Containers\ResourceLayoutContainer;
use Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface;

/**
 * @property Resource $resource
 *
 * ID главной страницы задаётся через {@see self::setHomeResourceId()} перед сериализацией:
 * при resolve() JsonResource может получить не тот же Request, что в контроллере (атрибуты не видны).
 */
class ResourceTreeResource extends JsonResource
{
    private static ?int $homeResourceId = null;

    public static function setHomeResourceId(?int $id): void
    {
        self::$homeResourceId = $id;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'slug' => $this->resource->slug,
            'status' => $this->resource->status,
            'title' => $this->resource->getTitle() ?? $this->resource->slug,
            'parent_id' => $this->resource->parent_id,
            'sort_order' => $this->resource->sort_order,
            'is_folder' => $this->resource->is_folder ?? false,
            'is_home' => $this->isHomeResource(),
            'resource_layout_id' => $this->resource->resource_layout_id,
            'layout' => $this->formatLayout($this->resource->resource_layout_id),
            'children' => $this->when(
                $this->relationLoaded('children') && $this->resource->children?->isNotEmpty(),
                fn () => ResourceTreeResource::collection($this->resource->children),
            ),
        ];
    }

    private function isHomeResource(): bool
    {
        return $this->resource->id === self::$homeResourceId;
    }

    private function formatLayout(?int $resourceLayoutId): array
    {
        if (!$resourceLayoutId) {
            return [
                'id' => null,
                'allow_children' => true,
                'children_layouts' => null,
                'is_catalog' => false,
            ];
        }

        /** @var ResourceLayoutRepositoryInterface $layoutsRepository */
        $layoutsRepository = resolve(ResourceLayoutRepositoryInterface::class);
        $layoutContainer = $layoutsRepository->findById($resourceLayoutId);

        return [
            'id' => $resourceLayoutId,
            'allow_children' => $layoutContainer->getLayout()->allowChildren(),
            'children_layouts' => $layoutContainer->getChildrenLayouts()
                ?->map(fn (ResourceLayoutContainer $child) => ResourceLayoutSimpleResource::make($child)->resolve())
                ->toArray(),
            'is_catalog' => $layoutContainer->getLayout()->getAttachedEntity() || $layoutContainer->getLayout()->getResourceCatalog(),
        ];
    }
}

