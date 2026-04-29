<?php

namespace Reno\Cms\Http\Resources\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Containers\ResourceLayoutContainer;

/**
 * @property ResourceLayoutContainer $resource
 */
class ResourceLayoutSimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getId(),
            'name' => $this->resource->getLayout()->getName(),
        ];
    }
}
