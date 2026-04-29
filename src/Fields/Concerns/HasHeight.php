<?php

namespace Reno\Cms\Fields\Concerns;

trait HasHeight
{
    protected ?int $height = null;

    public function height(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    protected function appendHeightConfiguration(array $configuration): array
    {
        if ($this->height !== null) {
            $configuration['height'] = $this->height;
        }

        return $configuration;
    }
}
