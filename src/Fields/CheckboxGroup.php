<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\HasOptionsConfiguration;
use Reno\Cms\Fields\Concerns\HasRequired;
use Reno\Cms\Interfaces\FieldTypes\HasOptions;
use Reno\Cms\FieldTypes\CheckboxGroupFieldType;

class CheckboxGroup extends AbstractField implements HasOptions
{
    use HasOptionsConfiguration;
    use HasRequired;

    public static function make(string $key): self
    {
        return new self($key, new CheckboxGroupFieldType());
    }

    public function getConfiguration(): array
    {
        return $this->appendOptionsConfiguration(parent::getConfiguration());
    }

    public function getValidationRules(): array
    {
        if ($this->required) {
            return ['required', 'array', 'min:1'];
        }

        return $this->fieldType->getValidationRules();
    }
}
