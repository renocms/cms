<?php

namespace Reno\Cms\Interfaces;

/**
 * Интерфейс для JavaScript-плагинов
 * 
 * Плагины позволяют кастомизировать поведение Vue-компонентов
 * без изменения основного кода компонентов
 */
interface JavascriptPluginInterface
{
    /**
     * Получить уникальное имя плагина
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Получить публичный URL JS-модуля плагина.
     *
     * @return string
     */
    public function getJsModule(): string;

    /**
     * Получить конфигурацию плагина
     * 
     * Может содержать дополнительные данные, которые будут переданы в JS-модуль
     * при инициализации плагина
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array;
}
