<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class RichContentFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'rich-content';
    }

    public function getName(): string
    {
        return 'Rich Content';
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_rich_content_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/RichContentEditor.vue');
    }

    public function getValidationRules(): array
    {
        return ['nullable', 'string'];
    }
}

