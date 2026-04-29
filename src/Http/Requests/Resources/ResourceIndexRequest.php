<?php

namespace Reno\Cms\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Reno\Cms\Helpers\TablePrefixHelper;

class ResourceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contextsTable = TablePrefixHelper::table('contexts');

        return [
            'context_id' => [
                'required',
                'integer',
                Rule::exists($contextsTable, 'id'),
            ],
            'ids' => [
                'nullable',
                'array',
            ],
            'ids.*' => [
                'integer',
            ],
        ];
    }
}
