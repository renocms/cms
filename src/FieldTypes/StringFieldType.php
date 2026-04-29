<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class StringFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'string';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_string_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_string_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/TextField.vue');
    }

    public function getValidationRules(): array
    {
        return ['nullable', 'string'];
    }

    public function hydrate(mixed $value): mixed
    {
        return $value ?? '';
    }
}
