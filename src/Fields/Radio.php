<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\HasOptionsConfiguration;
use Reno\Cms\Fields\Concerns\HasRequired;
use Reno\Cms\FieldTypes\RadioFieldType;
use Reno\Cms\Interfaces\FieldTypes\HasOptions;

class Radio extends AbstractField implements HasOptions
{
    use HasOptionsConfiguration;
    use HasRequired;

    protected ?bool $horizontal = null;

    public static function make(string $key): self
    {
        return new self($key, new RadioFieldType());
    }

    public function horizontal(bool $horizontal = true): static
    {
        $this->horizontal = $horizontal;

        return $this;
    }

    public function getConfiguration(): array
    {
        $config = $this->appendOptionsConfiguration(parent::getConfiguration());

        if ($this->horizontal !== null) {
            $config['horizontal'] = $this->horizontal;
        }

        return $config;
    }

    public function getValidationRules(): array
    {
        return $this->appendRequiredValidationRule($this->fieldType->getValidationRules());
    }
}
