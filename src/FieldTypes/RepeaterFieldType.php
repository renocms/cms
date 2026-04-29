<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class RepeaterFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'repeater';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_repeater_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_repeater_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/RepeaterEditor.vue');
    }

    public function getValidationRules(): array
    {
        return [];
    }

    public function dehydrate(mixed $value): mixed
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values($value);
    }

    public function hydrate(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values($value);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? array_values($decoded) : [];
        }

        return [];
    }
}
