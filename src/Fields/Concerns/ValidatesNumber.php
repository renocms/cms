<?php

namespace Reno\Cms\Fields\Concerns;

trait ValidatesNumber
{
    protected ?float $min = null;

    protected ?float $max = null;

    protected ?float $step = null;

    protected bool $required = false;

    public function min(float $min): static
    {
        $this->min = $min;

        return $this;
    }

    public function max(float $max): static
    {
        $this->max = $max;

        return $this;
    }

    public function step(float $step): static
    {
        $this->step = $step;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    protected function getNumberValidationRules(): array
    {
        $rules = ['numeric'];

        if ($this->required) {
            array_unshift($rules, 'required');
        } else {
            $rules[] = 'nullable';
        }

        if ($this->min !== null) {
            $rules[] = 'min:' . $this->min;
        }

        if ($this->max !== null) {
            $rules[] = 'max:' . $this->max;
        }

        return $rules;
    }
}
