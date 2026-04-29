<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class SelectFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'select';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_select_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_select_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/Select.vue');
    }

    public function getValidationRules(): array
    {
        return ['nullable'];
    }
}
