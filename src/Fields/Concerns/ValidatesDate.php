<?php

namespace Reno\Cms\Fields\Concerns;

trait ValidatesDate
{
    protected ?string $before = null;

    protected ?string $after = null;

    protected bool $required = false;

    public function before(string $date): static
    {
        $this->before = $date;

        return $this;
    }

    public function after(string $date): static
    {
        $this->after = $date;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    protected function getDateValidationRules(): array
    {
        $rules = ['date'];

        if ($this->required) {
            array_unshift($rules, 'required');
        } else {
            $rules[] = 'nullable';
        }

        if ($this->before !== null) {
            $rules[] = 'before:' . $this->before;
        }

        if ($this->after !== null) {
            $rules[] = 'after:' . $this->after;
        }

        return $rules;
    }
}
