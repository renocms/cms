<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class DateFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'date';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_date_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_date_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/DateEditor.vue');
    }

    public function getValidationRules(): array
    {
        return ['nullable', 'date'];
    }

    public function dehydrate(mixed $value): mixed
    {
        // Преобразуем значение для сохранения в БД
        if (empty($value)) {
            return null;
        }
        
        return $value;
    }

    public function hydrate(mixed $value): mixed
    {
        // Преобразуем значение из БД для отображения в компоненте
        if (empty($value)) {
            return '';
        }
        
        return $value;
    }
}
