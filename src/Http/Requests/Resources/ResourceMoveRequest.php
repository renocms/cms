<?php

namespace Reno\Cms\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;

class ResourceMoveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:' . \Reno\Cms\Helpers\TablePrefixHelper::table('resources') . ',id'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
