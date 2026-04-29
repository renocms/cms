<?php

namespace Reno\Cms\Interfaces\Repositories;

use Illuminate\Support\Collection;
use Reno\Cms\DTO\Settings\SettingForUpdate;
use Reno\Cms\Models\Setting;

interface SettingRepositoryInterface
{
    public const string HOME_RESOURCE_SETTING_KEY = 'home_resource_id';

    /**
     * @return Collection<Setting>
     */
    public function getByContext(int $contextId): Collection;

    public function findByKey(int $contextId, string $key): ?Setting;

    public function getHomeResourceId(int $contextId): ?int;

    public function updateOrCreate(SettingForUpdate $dto): Setting;

    /**
     * @return Collection<Setting>
     */
    public function updateMany(int $contextId, array $settings): Collection;

    public function delete(int $id): bool;
}
