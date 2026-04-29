<?php

namespace Reno\Cms\Fields\Concerns;

trait HasOptionsConfiguration
{
    protected ?array $options = null;

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    protected function appendOptionsConfiguration(array $configuration): array
    {
        if ($this->options !== null) {
            $configuration['options'] = $this->options;
        }

        return $configuration;
    }
}
