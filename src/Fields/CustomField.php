<?php

namespace Reno\Cms\Fields;

use InvalidArgumentException;
use Reno\Cms\Fields\Concerns\HasRequired;
use Reno\Cms\Interfaces\Forms\FieldInterface;
use Reno\Cms\Interfaces\Forms\CustomFieldInterface;
use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;
use Reno\Cms\Interfaces\Repositories\FieldTypeRepositoryInterface;

class CustomField extends AbstractField implements CustomFieldInterface
{
    use HasRequired;

    public static function make(string $key, FieldTypeInterface|string $fieldType): FieldInterface
    {
        if ($fieldType instanceof FieldTypeInterface) {
            $fieldTypeInstance = $fieldType;
        } elseif (is_string($fieldType) && class_exists($fieldType)) {
            $fieldTypeInstance = new $fieldType();
        } else {
            /** @var FieldTypeRepositoryInterface $fieldTypeRepository */
            $fieldTypeRepository = app(FieldTypeRepositoryInterface::class);
            $fieldTypeInstance = $fieldTypeRepository->findByType($fieldType);
            if ($fieldTypeInstance === null) {
                throw new InvalidArgumentException("Field type '{$fieldType}' not found.");
            }
        }

        return new self($key, $fieldTypeInstance);
    }

    public function getValidationRules(): array
    {
        return $this->appendRequiredValidationRule($this->fieldType->getValidationRules());
    }
}
