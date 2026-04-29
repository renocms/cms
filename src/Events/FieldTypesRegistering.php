<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

/**
 * Событие для регистрации типов полей
 * 
 * Запускается из FieldTypeRepository при получении всех типов полей.
 * Подключаемые модули могут слушать это событие и регистрировать свои типы полей.
 * 
 * Пример использования в ServiceProvider модуля:
 * 
 * ```php
 * use Illuminate\Support\Facades\Event;
 * use Reno\Cms\Events\FieldTypesRegistering;
 * use App\Reno\FieldTypes\CustomFieldType;
 * 
 * public function boot(): void
 * {
 *     Event::listen(FieldTypesRegistering::class, function (FieldTypesRegistering $event) {
 *         $event->addFieldType(new CustomFieldType());
 *     });
 * }
 * ```
 */
class FieldTypesRegistering
{
    use Dispatchable, SerializesModels;

    /**
     * @var array<FieldTypeInterface>
     */
    protected array $fieldTypes = [];

    public function addFieldType(FieldTypeInterface $fieldType): void
    {
        $this->fieldTypes[] = $fieldType;
    }

    /**
     * @return array<FieldTypeInterface>
     */
    public function getFieldTypes(): array
    {
        return $this->fieldTypes;
    }
}

