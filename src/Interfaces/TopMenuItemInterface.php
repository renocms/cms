<?php

namespace Reno\Cms\Interfaces;

/**
 * Интерфейс для плагинов пунктов меню в шапке админ-панели
 * 
 * Плагины позволяют кастомизировать меню админ-панели
 * без изменения основного кода
 */
interface TopMenuItemInterface
{
    /**
     * Получить уникальный идентификатор пункта меню
     * 
     * Используется для идентификации и переопределения пунктов меню
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Получить текст пункта меню
     * 
     * Может быть ключом для перевода через $t()
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Получить путь роута для простых ссылок
     * 
     * Опционально, если есть вложенные элементы
     *
     * @return string|null
     */
    public function getPath(): ?string;

    /**
     * Получить ID родительского пункта меню
     * 
     * Если null - корневой пункт
     *
     * @return string|null
     */
    public function getParentId(): ?string;

    /**
     * Добавить дочерний пункт меню
     *
     * @param TopMenuItemInterface $child
     * @return void
     */
    public function addChild(TopMenuItemInterface $child): void;

    /**
     * Получить вложенные пункты меню
     *
     * @return array<TopMenuItemInterface>
     */
    public function getChildren(): array;

    /**
     * Получить порядок сортировки пункта меню
     * 
     * Меньше = выше в списке
     *
     * @return int
     */
    public function getOrder(): int;

    /**
     * Получить иконку пункта меню
     *
     * @return string|null
     */
    public function getIcon(): ?string;

    /**
     * Проверить видимость пункта меню
     *
     * @return bool
     */
    public function isVisible(): bool;
}
