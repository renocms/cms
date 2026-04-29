<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Routing\Router;

/**
 * Событие для регистрации кастомных роутов внутри админки
 * 
 * Запускается из RenoServiceProvider внутри группы роутов с префиксом админки.
 * Подключаемые модули могут слушать это событие и регистрировать свои роуты.
 * 
 * Пример использования в ServiceProvider модуля:
 * 
 * ```php
 * use Illuminate\Support\Facades\Event;
 * use Reno\Cms\Events\AdminRoutesRegistering;
 * use Illuminate\Support\Facades\Route;
 * 
 * public function boot(): void
 * {
 *     Event::listen(AdminRoutesRegistering::class, function (AdminRoutesRegistering $event) {
 *         // Регистрация обычных роутов (будут доступны по адресу /{prefix}/custom)
 *         Route::get('/custom', [CustomController::class, 'index']);
 *     });
 * }
 * ```
 */
class AdminRoutesRegistering
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Router $router
    )
    {
    }
}

