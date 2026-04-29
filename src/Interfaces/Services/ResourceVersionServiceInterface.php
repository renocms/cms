<?php

namespace Reno\Cms\Interfaces\Services;

interface ResourceVersionServiceInterface
{
    /**
     * Создать версию ресурса
     *
     * @param int $resourceId
     * @return void
     */
    public function create(int $resourceId): void;
}

