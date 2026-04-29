<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\HasOptionsConfiguration;
use Reno\Cms\Fields\Concerns\HasRequired;
use Reno\Cms\FieldTypes\SelectFieldType;
use Reno\Cms\Interfaces\FieldTypes\HasOptions;

class Select extends AbstractField implements HasOptions
{
    use HasOptionsConfiguration;
    use HasRequired;

    public static function make(string $key): self
    {
        return new self($key, new SelectFieldType());
    }

    public function getConfiguration(): array
    {
        return $this->appendOptionsConfiguration(parent::getConfiguration());
    }

    public function getValidationRules(): array
    {
        return $this->appendRequiredValidationRule($this->fieldType->getValidationRules());
    }
}
