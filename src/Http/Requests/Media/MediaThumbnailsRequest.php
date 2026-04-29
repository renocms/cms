<?php

namespace Reno\Cms\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class MediaThumbnailsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'min:1'],
            'width' => ['nullable', 'integer', 'min:16', 'max:512'],
            'height' => ['nullable', 'integer', 'min:16', 'max:512'],
            'options' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
