<?php

namespace Reno\Cms\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Interfaces\DashboardBlockInterface;

class DashboardBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var DashboardBlockInterface $block */
        $block = $this->resource;

        return [
            'js_module' => $block->getJsModule(),
            'data' => $block->getData(),
        ];
    }
}

