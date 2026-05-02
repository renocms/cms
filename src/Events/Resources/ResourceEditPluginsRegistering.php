<?php

namespace Reno\Cms\Events\Resources;

use Reno\Cms\Events\AbstractJavascriptPluginRegistering;

/**
 * Событие для регистрации плагинов страницы редактирования ресурса
 *
 * Запускается из ResourceController при получении списка плагинов.
 * Подключаемые модули могут слушать это событие и регистрировать свои плагины.
 *
 * Пример использования в ServiceProvider модуля:
 *
 * ```php
 * use Illuminate\Support\Facades\Event;
 * use Reno\Cms\Events\Resources\ResourceEditPluginsRegistering;
 * use App\Reno\Plugins\SlugGeneratorPlugin;
 *
 * public function boot(): void
 * {
 *     Event::listen(ResourceEditPluginsRegistering::class, function (ResourceEditPluginsRegistering $event) {
 *         $event->add(new SlugGeneratorPlugin());
 *     });
 * }
 * ```
 */
class ResourceEditPluginsRegistering extends AbstractJavascriptPluginRegistering
{
}
