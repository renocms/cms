<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\HasHeight;
use Reno\Cms\Fields\Concerns\ValidatesString;
use Reno\Cms\FieldTypes\TextareaFieldType;

class Textarea extends AbstractField
{
    use HasHeight;
    use ValidatesString;

    protected ?int $rows = null;

    public static function make(string $key): self
    {
        return new self($key, new TextareaFieldType());
    }

    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function getConfiguration(): array
    {
        $config = $this->appendHeightConfiguration(parent::getConfiguration());

        if ($this->rows !== null) {
            $config['rows'] = $this->rows;
        }

        return $config;
    }

    public function getValidationRules(): array
    {
        return $this->getStringValidationRules();
    }
}
