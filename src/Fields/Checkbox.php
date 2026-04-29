<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\HasRequired;
use Reno\Cms\FieldTypes\CheckboxFieldType;

class Checkbox extends AbstractField
{
    use HasRequired;

    public static function make(string $key): self
    {
        return new self($key, new CheckboxFieldType());
    }

    public function getValidationRules(): array
    {
        return $this->appendRequiredValidationRule($this->fieldType->getValidationRules());
    }
}
