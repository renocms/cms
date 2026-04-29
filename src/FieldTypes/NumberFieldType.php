<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class NumberFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'number';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_number_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_number_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/NumberField.vue');
    }

    public function getValidationRules(): array
    {
        return ['nullable', 'numeric'];
    }

    public function dehydrate(mixed $value): mixed
    {
        return $value !== null && $value !== '' ? (float) $value : null;
    }

    public function hydrate(mixed $value): mixed
    {
        return $value !== null ? (float) $value : null;
    }
}
