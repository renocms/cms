<?php

namespace Reno\Cms\Facades;

use Reno\Cms\Fields\CustomField;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Reno\Cms\Interfaces\Forms\FieldInterface make(string $key, \Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface|string $fieldType)
 *
 * @see \Reno\Cms\Fields\CustomField
 */
class Field extends Facade
{
    public static function __callStatic($method, $args): mixed
    {
        return CustomField::$method(...$args);
    }
}
