<?php

namespace Reno\Cms\Http\Resources\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Interfaces\Contexts\ContextInterface;
use Reno\Cms\Models\Context;

class ResourceEditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'slug' => $this->resource->slug,
            'status' => $this->resource->status,
            'sort_order' => $this->resource->sort_order,
            'show_in_menu' => (bool) ($this->resource->show_in_menu ?? true),
            'is_folder' => (bool) ($this->resource->is_folder ?? false),
            'title' => $this->resource->getTitle() ?? $this->resource->slug,
            'resource_type_id' => $this->resource->resource_type_id,
            'resource_layout_id' => $this->resource->resource_layout_id,
            'parent_id' => $this->resource->parent_id,
            'values' => $this->when(
                $this->resource->relationLoaded('resourceValues')
                && $this->resource->resourceValues !== null,
                fn () => ResourceValueResource::collection($this->resource->resourceValues)->resolve(),
                []
            ),
            'context_id' => $this->resource->context_id,
            'context' => $this->whenLoaded('context', fn () => [
                'id' => $this->resource->context->id,
                'name' => $this->resolveContextLabel($this->resource->context),
            ]),
            'js_module' => $this->resource->resourceType?->getJsModule(),
        ];
    }

    private function resolveContextLabel(Context $contextModel): string
    {
        $className = $contextModel->class;

        if ($className === '' || !class_exists($className)) {
            return '';
        }

        if (!is_subclass_of($className, ContextInterface::class)) {
            return $className;
        }

        /** @var ContextInterface $context */
        $context = app($className);

        return $context->getLabel();
    }
}

