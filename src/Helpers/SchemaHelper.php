<?php

namespace Reno\Cms\Helpers;

use Reno\Cms\Fields\Repeater;
use Reno\Cms\Fields\Media;
use Illuminate\Support\Collection;
use Reno\Cms\Containers\FieldContainer;
use Reno\Cms\Interfaces\Forms\HasSchema;
use Reno\Cms\Interfaces\Forms\FieldInterface;

class SchemaHelper
{
    /**
     * @return Collection<FieldContainer>
     */
    public static function getFields(array $schema): Collection
    {
        $result = Collection::make();

        foreach ($schema as $element) {
            if ($element instanceof HasSchema) {
                $result = $result->merge(self::getFields($element->getSchema()));
            } else if ($element instanceof FieldContainer) {
                $result->add($element);
            } else if ($element instanceof FieldInterface) {
                $result->add(new FieldContainer(0, $element));
            }
        }

        return $result;
    }

    public static function collectMediaFields(Collection $fields, array $values, ?string $path = null): array
    {
        $result = [];

        /** @var FieldContainer $fieldContainer */
        foreach ($fields as $fieldContainer) {
            $field = $fieldContainer->getField();
            $key = $field->getKey();
            $value = $values[$key] ?? null;

            if (is_numeric($value) && $field instanceof Media) {
                $result[$value] = $path ? ($path . $key) : $key;
            } else if ($field instanceof Repeater) {
                foreach ($value ?? [] as $index => $rowValues) {
                    foreach (self::collectMediaFields(Collection::make($field->getSchema()), $rowValues, "$key.$index.") as $rowKey => $rowValue) {
                        $result[$rowKey] = $rowValue;
                    }
                }
            }
        }

        return $result;
    }
}
