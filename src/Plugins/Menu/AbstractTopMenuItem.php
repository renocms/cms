<?php

namespace Reno\Cms\Plugins\Menu;

use Reno\Cms\Interfaces\TopMenuItemInterface;

/**
 * Абстрактный класс для плагинов пунктов меню
 * 
 * Предоставляет базовую реализацию методов addChild и getChildren
 */
abstract class AbstractTopMenuItem implements TopMenuItemInterface
{
    /**
     * @var array<TopMenuItemInterface>
     */
    private array $children = [];

    /**
     * Добавить дочерний пункт меню
     *
     * @param TopMenuItemInterface $child
     * @return void
     */
    public function addChild(TopMenuItemInterface $child): void
    {
        $this->children[] = $child;
    }

    /**
     * Получить вложенные пункты меню
     *
     * @return array<TopMenuItemInterface>
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
