<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\Interfaces\Resources\ResourceTypeInterface;

/**
 * Событие для регистрации типов ресурсов
 * 
 * Запускается из ResourceTypeRepository при получении всех типов ресурсов.
 * Подключаемые модули могут слушать это событие и регистрировать свои типы ресурсов.
 * 
 * Пример использования в ServiceProvider модуля:
 * 
 * ```php
 * use Illuminate\Support\Facades\Event;
 * use Reno\Cms\Events\ResourceTypesRegistering;
 * use App\Reno\ResourceTypes\ProductResourceType;
 * 
 * public function boot(): void
 * {
 *     Event::listen(ResourceTypesRegistering::class, function (ResourceTypesRegistering $event) {
 *         $event->addResourceType(new ProductResourceType());
 *     });
 * }
 * ```
 */
class ResourceTypesRegistering
{
    use Dispatchable, SerializesModels;

    /**
     * @var array<ResourceTypeInterface>
     */
    protected array $resourceTypes = [];

    public function addResourceType(ResourceTypeInterface $resourceType): void
    {
        $this->resourceTypes[] = $resourceType;
    }

    /**
     * @return array<ResourceTypeInterface>
     */
    public function getResourceTypes(): array
    {
        return $this->resourceTypes;
    }
}
