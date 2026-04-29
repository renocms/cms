<?php

namespace Reno\Cms\Fields\Concerns;

trait ValidatesArray
{
    protected ?int $minItems = null;

    protected ?int $maxItems = null;

    protected bool $required = false;

    public function minItems(int $min): static
    {
        $this->minItems = $min;

        return $this;
    }

    public function maxItems(int $max): static
    {
        $this->maxItems = $max;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    protected function getArrayValidationRules(): array
    {
        $rules = ['array'];

        if ($this->required) {
            array_unshift($rules, 'required');
        } else {
            $rules[] = 'nullable';
        }

        if ($this->minItems !== null) {
            $rules[] = 'min:' . $this->minItems;
        }

        if ($this->maxItems !== null) {
            $rules[] = 'max:' . $this->maxItems;
        }

        return $rules;
    }
}
