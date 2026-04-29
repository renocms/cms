<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;
use Reno\Cms\Interfaces\FieldTypes\SyncsResourceValueInterface;
use Reno\Cms\Models\ResourceValue;

class MediaFieldType extends AbstractFieldType implements FieldTypeInterface, SyncsResourceValueInterface
{
    public function getType(): string
    {
        return 'media';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_media_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_media_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/MediaEditor.vue');
    }

    public function getValidationRules(): array
    {
        return ['nullable', 'integer'];
    }

    public function dehydrate(mixed $value): mixed
    {
        // Преобразуем значение для сохранения в БД
        if (empty($value)) {
            return null;
        }
        
        return is_numeric($value) ? (int) $value : null;
    }

    public function hydrate(mixed $value): mixed
    {
        // Преобразуем значение из БД для отображения в компоненте
        if (empty($value)) {
            return null;
        }
        
        return is_numeric($value) ? (int) $value : null;
    }

    public function syncResourceValue(ResourceValue $resourceValue, mixed $value): void
    {
        $dehydrated = $this->dehydrate($value);
        if ($dehydrated === null) {
            $resourceValue->update([
                'value' => '',
                'media_id' => null,
            ]);

            return;
        }

        $resourceValue->update([
            'value' => (string) $dehydrated,
            'media_id' => $dehydrated,
        ]);
    }

    public function deleteResourceValue(ResourceValue $resourceValue): void
    {
    }
}

