<?php

namespace Reno\Cms\Events\Resources;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\Interfaces\Layouts\ResourceLayoutInterface;

/**
 * Событие для регистрации макетов ресурсов
 *
 * Запускается из ResourceLayoutRepository при получении всех макетов.
 * По умолчанию классы из каталога `cms.layouts_path` подключаются через ClassesDiscoverer
 * (см. `cms.discover_layouts`). Подключаемые модули могут слушать это событие и добавлять макеты.
 *
 * Пример:
 *
 * ```php
 * Event::listen(ResourceLayoutsRegistering::class, function (ResourceLayoutsRegistering $event) {
 *     $event->addLayout(new CustomLayout());
 * });
 * ```
 */
class ResourceLayoutsRegistering
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var array<ResourceLayoutInterface>
     */
    protected array $layouts = [];

    public function addLayout(ResourceLayoutInterface $layout): void
    {
        $this->layouts[] = $layout;
    }

    /**
     * @return array<ResourceLayoutInterface>
     */
    public function getLayouts(): array
    {
        return $this->layouts;
    }
}
