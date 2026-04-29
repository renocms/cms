<?php

namespace Reno\Cms\Http\Resources\Resources;

use Illuminate\Http\Request;
use Reno\Cms\Models\ResourceValue;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Interfaces\Repositories\FieldTypeRepositoryInterface;
use Reno\Cms\Http\Resources\Media\MediaResource;

/**
 * @property ResourceValue $resource
 */
class ResourceValueResource extends JsonResource
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
            'resource_field_id' => $this->resource->resource_field_id,
            'value' => $this->getHydratedValue(),
            'media' => $this->when(
                $this->resource->relationLoaded('media') && $this->resource->media !== null,
                fn() => new MediaResource($this->resource->media),
            ),
        ];
    }

    private function getHydratedValue(): mixed
    {
        /** @var FieldTypeRepositoryInterface $fieldTypeRepository */
        $fieldTypeRepository = app(FieldTypeRepositoryInterface::class);
        $fieldType = $fieldTypeRepository->findByFieldId($this->resource->resource_field_id);

        if ($fieldType === null) {
            return $this->resource->value;
        }

        return $fieldType->hydrate($this->resource->value);
    }
}

