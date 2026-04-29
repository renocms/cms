<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class CheckboxGroupFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'checkbox_group';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_checkbox_group_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_checkbox_group_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/CheckboxGroup.vue');
    }

    public function getValidationRules(): array
    {
        return ['nullable', 'array'];
    }

    public function dehydrate(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $normalized = [];
        foreach ($value as $item) {
            if ($item === null || $item === '') {
                continue;
            }
            $normalized[] = (string) $item;
        }

        return array_values(array_unique($normalized));
    }

    public function hydrate(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $this->dehydrate($value);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $this->dehydrate($decoded) : [];
        }

        return [];
    }
}
