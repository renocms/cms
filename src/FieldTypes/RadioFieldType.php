<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class RadioFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'radio';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_radio_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_radio_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/RadioButtons.vue');
    }

    public function getValidationRules(): array
    {
        return ['nullable'];
    }
}
