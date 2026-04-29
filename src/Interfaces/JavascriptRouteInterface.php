<?php

namespace Reno\Cms\Interfaces;

/**
 * Интерфейс для плагинов роутинга Vue Router
 * 
 * Плагины позволяют кастомизировать роутинг админ-панели
 * без изменения основного кода
 */
interface JavascriptRouteInterface
{
    /**
     * Получить уникальное имя роута
     * 
     * Используется для идентификации и переопределения роутов
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Получить путь роута
     * 
     * Путь относительно /${adminPrefix}
     * Например: 'users', 'users/:id', '' (для dashboard)
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Получить публичный URL JS-модуля компонента роута.
     *
     * @return string
     */
    public function getJsModule(): string;

    /**
     * Получить дополнительные мета-данные роута
     *
     * @return array<string, mixed>
     */
    public function getMeta(): array;
}
