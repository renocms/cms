<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Containers\FieldContainer;
use Reno\Cms\Fields\Concerns\HasRequired;
use Reno\Cms\FieldTypes\RepeaterFieldType;
use Reno\Cms\Interfaces\Forms\FieldInterface;
use Reno\Cms\Http\Resources\Resources\ResourceLayoutFieldResource;

class Repeater extends AbstractField
{
    use HasRequired;

    /** @var array<int, FieldContainer> */
    protected array $schema = [];

    public static function make(string $key): static
    {
        return new static($key, new RepeaterFieldType());
    }

    /**
     * @param  array<int, FieldInterface>  $schema
     */
    public function schema(array $schema): static
    {
        foreach ($schema as $field) {
            if (!$field instanceof FieldInterface) {
                throw new \InvalidArgumentException('Each schema item must implement FieldInterface');
            }
        }

        $this->schema = array_map(
            static fn (FieldInterface $field) => new FieldContainer(0, $field),
            $schema,
        );

        return $this;
    }

    public function getConfiguration(): array
    {
        return array_merge(parent::getConfiguration(), [
            'display' => 'list',
            'schema' => array_map(
                static fn (FieldContainer $field) => ResourceLayoutFieldResource::make($field)->resolve(),
                $this->schema,
            ),
        ]);
    }

    public function getValidationRules(): array
    {
        $rules = [
            '' => $this->required ? ['required', 'array'] : ['nullable', 'array'],
        ];

        foreach ($this->schema as $field) {
            $merged = $this->mergeInnerFieldRules($field->getField()->getKey(), $field->getField()->getValidationRules());
            foreach ($merged as $key => $value) {
                $rules[$key] = $value;
            }
        }

        return $rules;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param  array<int|string, mixed>  $innerRules
     * @return array<string, mixed>
     */
    private function mergeInnerFieldRules(string $innerKey, array $innerRules): array
    {
        $prefix = '*.*.' . $innerKey;

        if (isset($innerRules[0]) && !is_array($innerRules[0])) {
            return [$prefix => array_values($innerRules)];
        }

        $result = [];
        foreach ($innerRules as $subKey => $subRules) {
            $subKey = trim((string) $subKey);
            $fullKey = $subKey === '' ? $prefix : $prefix . '.' . $subKey;
            $result[$fullKey] = $subRules;
        }

        return $result;
    }

    public function getSchema(): array
    {
        return $this->schema;
    }
}
