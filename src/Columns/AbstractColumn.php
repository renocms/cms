<?php

namespace Reno\Cms\Columns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Reno\Cms\Interfaces\Resources\ResourceInterface;
use Reno\Cms\Models\Resource;
use Reno\Cms\Models\ResourceValue;

abstract class AbstractColumn
{
    protected string $label;

    protected ?Closure $formatter = null;

    protected ?string $width = null;

    public function __construct(
        protected readonly string $key,
    ) {
        $this->label = $key;
    }

    public static function make(string $key): static
    {
        return new static($key);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function format(Closure $formatter): static
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function width(string $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function toArray(): array
    {
        $data = [
            'key' => $this->getKey(),
            'label' => $this->getLabel(),
            'type' => $this->getType(),
        ];

        if ($this->width !== null) {
            $data['width'] = $this->width;
        }

        return $data;
    }

    abstract public function getType(): string;

    protected function getResourceValue(ResourceInterface $resource): ?ResourceValue
    {
        if (!$resource instanceof Resource) {
            return null;
        }

        if (!$resource->relationLoaded('resourceValues')) {
            $resource->load('resourceValues.resourceField', 'resourceValues.media');
        }

        return $resource->resourceValues->first(
            fn (ResourceValue $resourceValue) => $resourceValue->resourceField?->key === $this->key
        );
    }

    protected function getAttributeValue(ResourceInterface $resource): mixed
    {
        if (!$resource instanceof Model) {
            return null;
        }

        $attributes = $resource->getAttributes();

        if (array_key_exists($this->key, $attributes)) {
            return $resource->getAttribute($this->key);
        }

        return null;
    }

    protected function formatValue(mixed $value): mixed
    {
        if ($this->formatter === null) {
            return $value;
        }

        return ($this->formatter)($value);
    }
}
