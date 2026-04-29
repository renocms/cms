<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\HasRequired;
use Reno\Cms\FieldTypes\MediaFieldType;

class Media extends AbstractField
{
    use HasRequired;

    protected ?string $accept = null;

    public static function make(string $key): self
    {
        return new self($key, new MediaFieldType());
    }

    public function accept(string $accept): static
    {
        $this->accept = $accept;

        return $this;
    }

    public function getConfiguration(): array
    {
        $config = parent::getConfiguration();

        if ($this->accept !== null) {
            $config['accept'] = $this->accept;
        }

        return $config;
    }

    public function getValidationRules(): array
    {
        return $this->appendRequiredValidationRule($this->fieldType->getValidationRules());
    }
}
