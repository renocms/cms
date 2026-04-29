<?php

namespace Reno\Cms\Interfaces\Services;

interface PathCacheInterface
{
    /**
     * Получить ID ресурса по пути и контексту
     *
     * @param int $contextId
     * @param string $path
     * @return int|null
     */
    public function get(int $contextId, string $path): ?int;

    /**
     * Получить путь ресурса по ID и контексту
     *
     * @param int $contextId
     * @param int $resourceId
     * @return string|null
     */
    public function getPathByResourceId(int $contextId, int $resourceId): ?string;

    /**
     * Сохранить путь ресурса в кэш
     *
     * @param int $contextId
     * @param string $path
     * @param int $resourceId
     * @return void
     */
    public function put(int $contextId, string $path, int $resourceId): void;

    /**
     * Удалить путь из кэша
     *
     * @param int $contextId
     * @param string $path
     * @return void
     */
    public function forget(int $contextId, string $path): void;

    /**
     * Очистить весь кэш для контекста
     *
     * @param int $contextId
     * @return void
     */
    public function clear(int $contextId): void;

    /**
     * Пересоздать кэш для контекста
     *
     * @param int $contextId
     * @return void
     */
    public function rebuild(int $contextId): void;
}

