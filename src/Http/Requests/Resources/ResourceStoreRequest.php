<?php

namespace Reno\Cms\Http\Requests\Resources;

class ResourceStoreRequest extends BaseResourceStoreRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'values' => [
                'array',
            ],
            'values.*.resource_field_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'values.*.value' => [
                'nullable',
            ],
        ]);
    }
}
