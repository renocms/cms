<?php

namespace Reno\Cms\Http\Resources\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Columns\AbstractColumn;
use Reno\Cms\Containers\ResourceLayoutContainer;
use Reno\Cms\Interfaces\Resources\ResourcesCatalogInterface;
use Reno\Cms\Models\Resource;

class ResourceCatalogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Resource $resource */
        $resource = $this->resource['resource'];

        /** @var ResourceLayoutContainer $layoutContainer */
        $layoutContainer = $this->resource['layout_container'];

        /** @var ResourcesCatalogInterface $catalog */
        $catalog = $this->resource['catalog'];

        /** @var Resource $catalogRoot */
        $catalogRoot = $this->resource['catalog_root'];

        return [
            'catalog_id' => (int) $catalogRoot->id,
            'resource' => [
                'id' => $resource->id,
                'title' => $resource->getTitle() ?? $resource->slug,
                'slug' => $resource->slug,
                'is_folder' => (bool) $resource->is_folder,
                'parent_id' => $resource->parent_id !== null ? (int) $resource->parent_id : null,
            ],
            'label' => $catalog->getLabel(),
            'allow_children' => $layoutContainer->getLayout()->allowChildren(),
            'children_layouts' => $layoutContainer->getChildrenLayouts()
                ?->map(fn ($child) => ResourceLayoutSimpleResource::make($child)->resolve())
                ->toArray(),
            'schema' => array_map(
                fn (AbstractColumn $column) => $column->toArray(),
                $catalog->getCatalogSchema(),
            ),
        ];
    }
}
