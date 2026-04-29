<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Routing\Router;

/**
 * Событие для регистрации кастомных API роутов внутри админки
 * 
 * Запускается из RenoServiceProvider внутри группы API роутов с префиксом админки.
 * Подключаемые модули могут слушать это событие и регистрировать свои API роуты.
 * 
 * Пример использования в ServiceProvider модуля:
 * 
 * ```php
 * use Illuminate\Support\Facades\Event;
 * use Reno\Cms\Events\AdminApiRoutesRegistering;
 * use Illuminate\Support\Facades\Route;
 * 
 * public function boot(): void
 * {
 *     Event::listen(AdminApiRoutesRegistering::class, function (AdminApiRoutesRegistering $event) {
 *         // Регистрация API роутов (будут доступны по адресу /{prefix}/api/custom)
 *         Route::get('/custom', [CustomApiController::class, 'index']);
 *     });
 * }
 * ```
 */
class AdminApiRoutesRegistering
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Router $router
    )
    {
    }
}

