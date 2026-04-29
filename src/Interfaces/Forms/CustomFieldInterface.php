<?php

namespace Reno\Cms\Interfaces\Forms;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

interface CustomFieldInterface extends FieldInterface
{
    public static function make(string $key, FieldTypeInterface|string $fieldType): FieldInterface;
}
