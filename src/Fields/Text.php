<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\ValidatesString;
use Reno\Cms\FieldTypes\StringFieldType;

class Text extends AbstractField
{
    use ValidatesString;

    public static function make(string $key): self
    {
        return new self($key, new StringFieldType());
    }

    public function getValidationRules(): array
    {
        return $this->getStringValidationRules();
    }
}
