<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class FileFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'file';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_file_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_file_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/File.vue');
    }

    public function getValidationRules(): array
    {
        return ['nullable', 'file'];
    }
}
