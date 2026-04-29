<?php

namespace Reno\Cms\Interfaces;

interface DashboardBlockInterface
{
    /**
     * Получить публичный URL JS-модуля блока.
     *
     * Например: '/vendor/reno/cms/build/custom-components/dashboard/ResourcesCount.js'
     *
     * @return string
     */
    public function getJsModule(): string;

    /**
     * Получить данные для блока
     *
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * Получить порядок сортировки блока
     * Блоки с меньшим значением отображаются первыми
     *
     * @return int
     */
    public function getSortOrder(): int;
}

