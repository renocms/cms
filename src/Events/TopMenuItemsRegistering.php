<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\Interfaces\TopMenuItemInterface;

/**
 * Событие для регистрации пунктов меню в шапке админ-панели
 * 
 * Запускается из DashboardController при получении списка пунктов меню.
 * Подключаемые модули могут слушать это событие и регистрировать свои пункты меню.
 * 
 * Пример использования в ServiceProvider модуля:
 * 
 * ```php
 * use Illuminate\Support\Facades\Event;
 * use Reno\Cms\Events\TopMenuItemsRegistering;
 * use App\Reno\Plugins\Menu\MyMenuItem;
 * 
 * public function boot(): void
 * {
 *     Event::listen(TopMenuItemsRegistering::class, function (TopMenuItemsRegistering $event) {
 *         $event->add(new MyMenuItem());
 *     });
 * }
 * ```
 */
class TopMenuItemsRegistering
{
    use Dispatchable, SerializesModels;

    /**
     * @var array<TopMenuItemInterface>
     */
    protected array $items = [];

    /**
     * Добавить пункт меню
     *
     * @param TopMenuItemInterface $menuItem
     * @return void
     */
    public function add(TopMenuItemInterface $menuItem): void
    {
        $parentId = $menuItem->getParentId();
        
        if ($parentId === null) {
            // Корневой пункт - добавляем в массив
            $this->items[] = $menuItem;
        } else {
            // Ищем родительский пункт среди уже добавленных
            $parent = null;
            foreach ($this->items as $item) {
                if ($item->getId() === $parentId) {
                    $parent = $item;
                    break;
                }
            }
            
            // Проверяем вложенные пункты рекурсивно
            if ($parent === null) {
                $parent = $this->findParentInChildren($this->items, $parentId);
            }
            
            if ($parent !== null) {
                // Родитель найден - добавляем как дочерний
                $parent->addChild($menuItem);
            } else {
                // Родитель не найден - добавляем как корневой
                $this->items[] = $menuItem;
            }
        }
    }

    /**
     * Рекурсивный поиск родителя во вложенных пунктах
     *
     * @param array<TopMenuItemInterface> $items
     * @param string $parentId
     * @return TopMenuItemInterface|null
     */
    private function findParentInChildren(array $items, string $parentId): ?TopMenuItemInterface
    {
        foreach ($items as $item) {
            $children = $item->getChildren();
            foreach ($children as $child) {
                if ($child->getId() === $parentId) {
                    return $child;
                }
                $found = $this->findParentInChildren([$child], $parentId);
                if ($found !== null) {
                    return $found;
                }
            }
        }
        
        return null;
    }

    /**
     * Получить все зарегистрированные пункты меню
     *
     * @return array<TopMenuItemInterface>
     */
    public function getAll(): array
    {
        return $this->items;
    }
}
