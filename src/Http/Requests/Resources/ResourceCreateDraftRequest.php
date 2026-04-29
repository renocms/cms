<?php

namespace Reno\Cms\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;
use Reno\Cms\Models\Context;
use Reno\Cms\Models\Resource;

class ResourceCreateDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                'exists:' . Resource::getTableName() . ',id',
            ],
            'context_id' => [
                'nullable',
                'integer',
                'exists:' . Context::getTableName() . ',id',
            ],
        ];
    }
}
