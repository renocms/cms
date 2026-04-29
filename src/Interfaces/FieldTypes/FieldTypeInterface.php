<?php

namespace Reno\Cms\Interfaces\FieldTypes;

interface FieldTypeInterface
{
    public function getType(): string;

    public function getName(): string;

    public function getDescription(): ?string;

    /**
     * Возвращает публичный URL JS-модуля для отображения/редактирования значения.
     *
     * Модуль должен быть доступен от корня сайта, например:
     * - '/vendor/reno/cms/build/custom-components/field-types/RichContentEditor.js'
     * - '/vendor/package-name/build/components/MyComponent.js'
     *
     * @return string Публичный URL JS-модуля компонента
     */
    public function getJsModule(): string;
    
    public function getValidationRules(): array;
    
    /**
     * Преобразование значения для сохранения в БД (дегидратация)
     */
    public function dehydrate(mixed $value): mixed;
    
    /**
     * Преобразование значения для отображения в компоненте (гидратация)
     */
    public function hydrate(mixed $value): mixed;
}

