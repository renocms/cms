<?php

namespace Reno\Cms\Http\Resources\Contexts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Containers\ContextContainer;

/**
 * @property ContextContainer $resource
 */
class ContextContainerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getId(),
            'name' => $this->resource->getContext()->getLabel(),
        ];
    }
}
