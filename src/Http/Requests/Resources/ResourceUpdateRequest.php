<?php

namespace Reno\Cms\Http\Requests\Resources;

class ResourceUpdateRequest extends BaseResourceUpdateRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'values' => 'array',
            'values.*.resource_field_id' => 'required|integer|min:1',
            'values.*.value' => 'nullable',
        ]);
    }
}
