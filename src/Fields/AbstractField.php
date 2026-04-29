<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Interfaces\Forms\FieldInterface;
use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

abstract class AbstractField implements FieldInterface
{
    use Concerns\HasConfiguration;

    public function __construct(
        protected string $key,
        protected FieldTypeInterface $fieldType,
    )
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return $this->label ?? ucfirst(str_replace('_', ' ', $this->key));
    }

    public function getDescription(): ?string
    {
        return $this->fieldType->getDescription();
    }

    public function getFieldType(): FieldTypeInterface
    {
        return $this->fieldType;
    }

    public function getValidationRules(): array
    {
        return $this->fieldType->getValidationRules();
    }

    public function isRequired(): bool
    {
        return in_array('required', $this->getValidationRules(), true);
    }
}
