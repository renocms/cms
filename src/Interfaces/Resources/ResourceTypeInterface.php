<?php

namespace Reno\Cms\Interfaces\Resources;

interface ResourceTypeInterface
{
    /**
     * Получить класс Eloquent модели для данного типа ресурса
     *
     * @return string Полный путь к классу модели, которая наследуется от Reno\Cms\Models\Resource
     */
    public function getResourceClass(): string;

    /**
     * Получить названия relations для загрузки связанных сущностей
     *
     * @return array<string> Массив названий relations (например: ['resourceValues', 'resourceValues.resourceField'])
     */
    public function getResourceRelations(): array;

    /**
     * Получить публичный URL JS-модуля для редактирования ресурса.
     *
     * @return string|null URL JS-модуля или null для использования стандартного компонента
     */
    public function getJsModule(): ?string;

    /**
     * Получить название типа ресурса
     */
    public function getName(): string;

    /**
     * Получить описание типа ресурса
     */
    public function getDescription(): ?string;

    /**
     * Получить иконку для отображения типа ресурса
     */
    public function getIcon(): ?string;

    /**
     * Поддерживает ли тип ресурса редактирование ResourceField
     */
    public function supportsResourceFields(): bool;
}

