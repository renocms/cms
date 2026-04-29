<?php

namespace Reno\Cms\Fields\Concerns;

trait ValidatesString
{
    protected ?int $minLength = null;

    protected ?int $maxLength = null;

    protected ?string $regex = null;

    protected bool $required = false;

    public function minLength(int $min): static
    {
        $this->minLength = $min;

        return $this;
    }

    public function maxLength(int $max): static
    {
        $this->maxLength = $max;

        return $this;
    }

    public function regex(string $pattern): static
    {
        $this->regex = $pattern;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    protected function getStringValidationRules(): array
    {
        $rules = ['string'];

        if ($this->required) {
            array_unshift($rules, 'required');
        } else {
            $rules[] = 'nullable';
        }

        if ($this->minLength !== null) {
            $rules[] = 'min:' . $this->minLength;
        }

        if ($this->maxLength !== null) {
            $rules[] = 'max:' . $this->maxLength;
        }

        if ($this->regex !== null) {
            $rules[] = 'regex:' . $this->regex;
        }

        return $rules;
    }
}
