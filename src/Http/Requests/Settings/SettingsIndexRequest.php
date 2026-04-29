<?php

namespace Reno\Cms\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Reno\Cms\Helpers\TablePrefixHelper;

class SettingsIndexRequest extends FormRequest
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
        ];
    }
}
