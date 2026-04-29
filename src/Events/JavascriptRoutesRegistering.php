<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\Interfaces\JavascriptRouteInterface;

/**
 * Событие для регистрации JavaScript-роутов
 * 
 * Запускается из DashboardController при получении списка роутов.
 * Подключаемые модули могут слушать это событие и регистрировать свои роуты.
 * 
 * Пример использования в ServiceProvider модуля:
 * 
 * ```php
 * use Illuminate\Support\Facades\Event;
 * use Reno\Cms\Events\JavascriptRoutesRegistering;
 * use App\Reno\Plugins\Routes\MyRoute;
 * 
 * public function boot(): void
 * {
 *     Event::listen(JavascriptRoutesRegistering::class, function (JavascriptRoutesRegistering $event) {
 *         $event->add(new MyRoute());
 *     });
 * }
 * ```
 */
class JavascriptRoutesRegistering
{
    use Dispatchable, SerializesModels;

    /**
     * @var array<JavascriptRouteInterface>
     */
    protected array $items = [];

    /**
     * Добавить роут
     *
     * @param JavascriptRouteInterface $route
     * @return void
     */
    public function add(JavascriptRouteInterface $route): void
    {
        $this->items[] = $route;
    }

    /**
     * Получить все зарегистрированные роуты
     *
     * @return array<JavascriptRouteInterface>
     */
    public function getAll(): array
    {
        return $this->items;
    }
}
