<?php

namespace Reno\Cms\Fields\Concerns;

trait ValidatesFile
{
    protected ?array $mimes = null;

    protected ?int $maxSize = null;

    protected bool $required = false;

    public function mimes(array $mimes): static
    {
        $this->mimes = $mimes;

        return $this;
    }

    public function maxSize(int $kilobytes): static
    {
        $this->maxSize = $kilobytes;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    protected function getFileValidationRules(): array
    {
        $rules = ['file'];

        if ($this->required) {
            array_unshift($rules, 'required');
        } else {
            $rules[] = 'nullable';
        }

        if ($this->mimes !== null) {
            $rules[] = 'mimes:' . implode(',', $this->mimes);
        }

        if ($this->maxSize !== null) {
            $rules[] = 'max:' . $this->maxSize;
        }

        return $rules;
    }
}
