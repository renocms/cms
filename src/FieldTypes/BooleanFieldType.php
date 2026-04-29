<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class BooleanFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'boolean';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_boolean_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_boolean_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/BooleanField.vue');
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
