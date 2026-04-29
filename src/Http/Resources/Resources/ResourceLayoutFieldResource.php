<?php

namespace Reno\Cms\Http\Resources\Resources;

use Illuminate\Http\Request;
use Reno\Cms\Containers\FieldContainer;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceLayoutFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var FieldContainer $fieldContainer */
        $fieldContainer = $this->resource;
        $field = $fieldContainer->getField();

        return [
            'element' => 'field',
            'id' => $fieldContainer->getId(),
            'key' => $field->getKey(),
            'name' => $field->getName(),
            'description' => $field->getDescription(),
            'type' => $field->getFieldType()->getType(),
            'is_required' => $field->isRequired(),
            'sort_order' => 0,
            'js_module' => $field->getFieldType()->getJsModule(),
            'configuration' => $field->getConfiguration(),
        ];
    }
}
