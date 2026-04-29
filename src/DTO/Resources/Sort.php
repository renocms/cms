<?php

namespace Reno\Cms\DTO\Resources;

use Reno\Cms\Exceptions\InvalidSortRuleException;

class Sort
{
    public const ASC = 'asc';
    public const DESC = 'desc';

    private const TYPE_VALUE = 'value';
    private const TYPE_RESOURCE = 'resource';

    private function __construct(
        private readonly string $type,
        private readonly string $field,
        private readonly string $direction,
    )
    {
    }

    public static function value(string $field, string $direction = self::ASC): self
    {
        return new self(
            type: self::TYPE_VALUE,
            field: self::validateField($field),
            direction: self::normalizeDirection($direction),
        );
    }

    public static function resource(string $field, string $direction = self::ASC): self
    {
        return new self(
            type: self::TYPE_RESOURCE,
            field: self::validateField($field),
            direction: self::normalizeDirection($direction),
        );
    }

    public function isValue(): bool
    {
        return $this->type === self::TYPE_VALUE;
    }

    public function isResource(): bool
    {
        return $this->type === self::TYPE_RESOURCE;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    private static function validateField(string $field): string
    {
        $normalizedField = trim($field);
        if ($normalizedField === '') {
            throw new InvalidSortRuleException('Sort field cannot be empty');
        }

        return $normalizedField;
    }

    private static function normalizeDirection(string $direction): string
    {
        $normalizedDirection = strtolower(trim($direction));
        if (!in_array($normalizedDirection, [self::ASC, self::DESC], true)) {
            throw new InvalidSortRuleException(
                sprintf(
                    'Unsupported sort direction "%s". Allowed values: %s, %s',
                    $direction,
                    self::ASC,
                    self::DESC,
                ),
            );
        }

        return $normalizedDirection;
    }
}
