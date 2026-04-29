<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\HasRequired;
use Reno\Cms\FieldTypes\ColorFieldType;

class Color extends AbstractField
{
    use HasRequired;

    public static function make(string $key): self
    {
        return new self($key, new ColorFieldType());
    }

    public function getValidationRules(): array
    {
        return $this->appendRequiredValidationRule($this->fieldType->getValidationRules());
    }
}
