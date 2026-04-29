<?php

namespace Reno\Cms\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;
use Reno\Cms\Models\Context;
use Reno\Cms\Models\Resource;
use Reno\Cms\Models\ResourceLayout;
use Reno\Cms\Models\ResourceType;

class BaseResourceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'context_id' => [
                'required',
                'integer',
                'exists:' . Context::getTableName() . ',id',
            ],
            'resource_type_id' => [
                'required',
                'integer',
                'exists:' . ResourceType::getTableName() . ',id',
            ],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:' . Resource::getTableName() . ',id',
            ],
            'resource_layout_id' => [
                'nullable',
                'integer',
                'exists:' . ResourceLayout::getTableName() . ',id',
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
            ],
            'status' => [
                'nullable',
                'string',
                'in:draft,published,archived',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'show_in_menu' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}
