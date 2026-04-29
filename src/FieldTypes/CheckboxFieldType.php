<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class CheckboxFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'checkbox';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_checkbox_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_checkbox_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/Checkboxes.vue');
    }

    public function getValidationRules(): array
    {
        return ['nullable', 'boolean'];
    }

    public function hydrate(mixed $value): bool
    {
        return (bool) $value;
    }
}
