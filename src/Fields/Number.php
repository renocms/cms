<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\ValidatesNumber;
use Reno\Cms\FieldTypes\NumberFieldType;

class Number extends AbstractField
{
    use ValidatesNumber;

    public static function make(string $key): self
    {
        return new self($key, new NumberFieldType());
    }

    public function getValidationRules(): array
    {
        return $this->getNumberValidationRules();
    }
}
