<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\HasHeight;
use Reno\Cms\Fields\Concerns\HasRequired;
use Reno\Cms\FieldTypes\RichContentFieldType;

class RichContent extends AbstractField
{
    use HasHeight;
    use HasRequired;

    public static function make(string $key): self
    {
        return new self($key, new RichContentFieldType());
    }

    public function getConfiguration(): array
    {
        return $this->appendHeightConfiguration(parent::getConfiguration());
    }

    public function getValidationRules(): array
    {
        return $this->appendRequiredValidationRule($this->fieldType->getValidationRules());
    }
}
