<?php

namespace Reno\Cms\DTO\Resources;

use Illuminate\Database\Eloquent\Builder;
use Reno\Cms\Exceptions\InvalidValueFilterException;
use Reno\Cms\Models\ResourceValue;

class ValueFilter
{
    private const OPERATOR_EQUALS = 'equals';
    private const OPERATOR_NOT_EQUALS = 'notEquals';
    private const OPERATOR_IN = 'in';
    private const OPERATOR_NOT_IN = 'notIn';

    private function __construct(
        private readonly string $field,
        private readonly string $operator,
        private readonly mixed $value = null,
        private readonly ?array $values = null,
    )
    {
    }

    public static function equals(string $field, mixed $value): self
    {
        return new self(
            field: self::validateField($field),
            operator: self::OPERATOR_EQUALS,
            value: $value,
        );
    }

    public static function notEquals(string $field, mixed $value): self
    {
        return new self(
            field: self::validateField($field),
            operator: self::OPERATOR_NOT_EQUALS,
            value: $value,
        );
    }

    public static function in(string $field, array $values): self
    {
        return new self(
            field: self::validateField($field),
            operator: self::OPERATOR_IN,
            values: self::normalizeValues($field, $values, self::OPERATOR_IN),
        );
    }

    public static function notIn(string $field, array $values): self
    {
        return new self(
            field: self::validateField($field),
            operator: self::OPERATOR_NOT_IN,
            values: self::normalizeValues($field, $values, self::OPERATOR_NOT_IN),
        );
    }

    public function addToQuery(Builder $query, string $resourceTable = 'resources'): void
    {
        $resourceValuesTable = ResourceValue::getTableName();

        $query->whereHas('resourceValues', function (Builder $resourceValuesQuery) use ($resourceTable, $resourceValuesTable) {
            $resourceValuesQuery
                ->whereColumn($resourceValuesTable . '.resource_id', $resourceTable . '.id')
                ->whereHas('resourceField', function (Builder $resourceFieldQuery) {
                    $resourceFieldQuery->where('key', $this->field);
                });

            $this->addValueConstraintToQuery($resourceValuesQuery, $resourceValuesTable);
        });
    }

    private function addValueConstraintToQuery(Builder $query, string $resourceValuesTable): void
    {
        $valueColumn = $resourceValuesTable . '.value';

        switch ($this->operator) {
            case self::OPERATOR_EQUALS:
                $query->where($valueColumn, '=', (string)$this->value);
                return;

            case self::OPERATOR_NOT_EQUALS:
                $query->where($valueColumn, '!=', (string)$this->value);
                return;

            case self::OPERATOR_IN:
                $query->whereIn($valueColumn, $this->values ?? []);
                return;

            case self::OPERATOR_NOT_IN:
                $query->whereNotIn($valueColumn, $this->values ?? []);
                return;
        }

        throw new InvalidValueFilterException('Unknown value filter operator: ' . $this->operator);
    }

    private static function validateField(string $field): string
    {
        $normalizedField = trim($field);

        if ($normalizedField === '') {
            throw new InvalidValueFilterException('Value filter field key cannot be empty');
        }

        return $normalizedField;
    }

    private static function normalizeValues(string $field, array $values, string $operator): array
    {
        if ($values === []) {
            throw new InvalidValueFilterException(
                sprintf('Value filter "%s" for field "%s" must contain at least one value', $operator, $field),
            );
        }

        $normalizedValues = array_map(static fn (mixed $value) => (string)$value, $values);
        if ($normalizedValues === []) {
            throw new InvalidValueFilterException(
                sprintf('Value filter "%s" for field "%s" contains invalid values', $operator, $field),
            );
        }

        return $normalizedValues;
    }
}
