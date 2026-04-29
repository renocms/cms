<?php

namespace Reno\Cms\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;
use Reno\Cms\Models\Resource;

class ResourceCatalogResourcesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('catalog_id') && $this->filled('resource_id')) {
            $this->merge([
                'catalog_id' => $this->input('resource_id'),
            ]);
        }

        if ($this->filled('catalog_id') && !$this->filled('resource_id')) {
            $this->merge([
                'resource_id' => $this->input('catalog_id'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'catalog_id' => [
                'required',
                'integer',
                'exists:' . Resource::getTableName() . ',id',
            ],
            'resource_id' => [
                'required',
                'integer',
                'exists:' . Resource::getTableName() . ',id',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }
}
