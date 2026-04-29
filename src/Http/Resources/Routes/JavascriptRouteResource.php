<?php

namespace Reno\Cms\Http\Resources\Routes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Interfaces\JavascriptRouteInterface;

class JavascriptRouteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var JavascriptRouteInterface $route */
        $route = $this->resource;

        return [
            'name' => $route->getName(),
            'path' => $route->getPath(),
            'js_module' => $route->getJsModule(),
            'meta' => $route->getMeta(),
        ];
    }
}
