<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class TextareaFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'textarea';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_textarea_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_textarea_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/Textarea.vue');
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
