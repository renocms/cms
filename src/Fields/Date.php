<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\ValidatesDate;
use Reno\Cms\FieldTypes\DateFieldType;

class Date extends AbstractField
{
    use ValidatesDate;

    public static function make(string $key): self
    {
        return new self($key, new DateFieldType());
    }
}
