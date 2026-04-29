<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

abstract class AbstractFieldType implements FieldTypeInterface
{
    public function getValidationRules(): array
    {
        return [];
    }

    public function dehydrate(mixed $value): mixed
    {
        return $value;
    }

    public function hydrate(mixed $value): mixed
    {
        return $value;
    }
}
