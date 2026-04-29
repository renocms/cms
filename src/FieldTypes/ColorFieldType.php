<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class ColorFieldType extends AbstractFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'color';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_color_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_color_description');
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('custom-components/field-types/ColorField.vue');
    }

    public function getValidationRules(): array
    {
        return ['nullable', 'string', 'regex:/^#?(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'];
    }

    public function dehydrate(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $normalizedColor = $this->normalizeColor($value);

        if ($normalizedColor !== null) {
            return $normalizedColor;
        }

        $trimmedValue = trim($value);

        return $trimmedValue === '' ? null : $trimmedValue;
    }

    public function hydrate(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $normalizedColor = $this->normalizeColor($value);

        if ($normalizedColor !== null) {
            return $normalizedColor;
        }

        $trimmedValue = trim($value);

        return $trimmedValue === '' ? null : $trimmedValue;
    }

    private function normalizeColor(string $value): ?string
    {
        $normalizedValue = ltrim(trim($value), '#');

        if ($normalizedValue === '') {
            return null;
        }

        if (!preg_match('/^[0-9a-fA-F]{3}$|^[0-9a-fA-F]{6}$/', $normalizedValue)) {
            return null;
        }

        if (strlen($normalizedValue) === 3) {
            $normalizedValue = sprintf(
                '%s%s%s%s%s%s',
                $normalizedValue[0],
                $normalizedValue[0],
                $normalizedValue[1],
                $normalizedValue[1],
                $normalizedValue[2],
                $normalizedValue[2],
            );
        }

        return '#' . strtolower($normalizedValue);
    }
}
