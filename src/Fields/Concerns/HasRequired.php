<?php

namespace Reno\Cms\Fields\Concerns;

trait HasRequired
{
    protected bool $required = false;

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    protected function appendRequiredValidationRule(array $rules): array
    {
        if ($this->required) {
            array_unshift($rules, 'required');
        }

        return $rules;
    }
}
