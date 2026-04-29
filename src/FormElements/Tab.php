<?php

namespace Reno\Cms\FormElements;

use Reno\Cms\Interfaces\Forms\HasSchema;
use Reno\Cms\Interfaces\Forms\FormElementInterface;

class Tab implements FormElementInterface, HasSchema
{
    /**
     * @param array<FormElementInterface> $schema
     */
    public function __construct(
        private string $name,
        private array $schema = [],
        private ?string $description = null,
    )
    {
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function description(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param array<FormElementInterface> $schema
     */
    public function schema(array $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @return array<FormElementInterface>
     */
    public function getSchema(): array
    {
        return $this->schema;
    }
}
