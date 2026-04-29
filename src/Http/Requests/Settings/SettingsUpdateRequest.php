<?php

namespace Reno\Cms\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Reno\Cms\Models\Context;

class SettingsUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'context_id' => [
                'required',
                'integer',
                'exists:' . Context::getTableName() . ',id',
            ],
            'settings' => 'required|array',
        ];
    }
}
