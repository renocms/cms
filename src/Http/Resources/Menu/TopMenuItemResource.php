<?php

namespace Reno\Cms\Http\Resources\Menu;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Interfaces\TopMenuItemInterface;

class TopMenuItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var TopMenuItemInterface $menuItem */
        $menuItem = $this->resource;

        $result = [
            'id' => $menuItem->getId(),
            'label' => $menuItem->getLabel(),
            'order' => $menuItem->getOrder(),
            'visible' => $menuItem->isVisible(),
        ];

        $path = $menuItem->getPath();
        if ($path !== null) {
            $result['path'] = $path;
        }

        $icon = $menuItem->getIcon();
        if ($icon !== null) {
            $result['icon'] = $icon;
        }

        $children = $menuItem->getChildren();
        if (!empty($children)) {
            $result['children'] = TopMenuItemResource::collection($children)->resolve($request);
        }

        return $result;
    }
}
