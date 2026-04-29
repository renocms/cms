<?php

namespace Reno\Cms\Fields\Concerns;

trait HasConfiguration
{
    protected ?string $label = null;

    protected ?string $placeholder = null;

    protected mixed $default = null;

    protected bool $defaultProvided = false;

    /** Подсказка под полем в админке (без локализации) */
    protected ?string $note = null;

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function placeholder(string $text): static
    {
        $this->placeholder = $text;

        return $this;
    }

    /**
     * Значение по умолчанию для новых записей. Допускается \Closure — вычисляется при сборке схемы.
     *
     * @param mixed|\Closure():mixed $value
     */
    public function default(mixed $value): static
    {
        $this->default = $value;
        $this->defaultProvided = true;

        return $this;
    }

    public function note(string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getDefault(): mixed
    {
        if (!$this->defaultProvided) {
            return null;
        }

        return $this->resolveDefaultValue();
    }

    public function getConfiguration(): array
    {
        $config = array_filter([
            'label' => $this->label,
            'placeholder' => $this->placeholder,
        ], static fn ($value) => $value !== null);

        if ($this->defaultProvided) {
            $config['default'] = $this->resolveDefaultValue();
        }

        if ($this->note !== null) {
            $config['note'] = $this->note;
        }

        return $config;
    }

    private function resolveDefaultValue(): mixed
    {
        if ($this->default instanceof \Closure) {
            return ($this->default)();
        }

        return $this->default;
    }
}
