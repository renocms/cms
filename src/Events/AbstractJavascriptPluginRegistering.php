<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\Interfaces\JavascriptPluginInterface;

/**
 * Абстрактный класс для регистрации JavaScript-плагинов
 * 
 * Базовый класс для событий регистрации плагинов различных компонентов.
 * Предоставляет общие методы для работы с плагинами.
 */
abstract class AbstractJavascriptPluginRegistering
{
    use Dispatchable, SerializesModels;

    /**
     * @var array<JavascriptPluginInterface>
     */
    protected array $plugins = [];

    /**
     * Добавить плагин
     *
     * @param JavascriptPluginInterface $plugin
     * @return void
     */
    public function add(JavascriptPluginInterface $plugin): void
    {
        $this->plugins[] = $plugin;
    }

    /**
     * Получить все зарегистрированные плагины
     *
     * @return array<JavascriptPluginInterface>
     */
    public function getAll(): array
    {
        return $this->plugins;
    }
}
