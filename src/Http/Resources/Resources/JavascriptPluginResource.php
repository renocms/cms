<?php

namespace Reno\Cms\Http\Resources\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Interfaces\JavascriptPluginInterface;

class JavascriptPluginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var JavascriptPluginInterface $plugin */
        $plugin = $this->resource;

        return [
            'name' => $plugin->getName(),
            'js_module' => $plugin->getJsModule(),
            'config' => $plugin->getConfig(),
        ];
    }
}
