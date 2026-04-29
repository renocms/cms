<?php

namespace Reno\Cms\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;
use Reno\Cms\Models\ResourceLayout;

class BaseResourceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource_layout_id' => [
                'nullable',
                'integer',
                'exists:' . ResourceLayout::getTableName() . ',id',
            ],
            'slug' => 'required|string|max:255',
            'status' => 'nullable|string|in:draft,published,archived',
            'sort_order' => 'nullable|integer|min:0',
            'show_in_menu' => 'nullable|boolean',
        ];
    }
}
