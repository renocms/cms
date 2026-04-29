<?php

namespace Reno\Cms\Repositories;

use Illuminate\Support\Collection;
use Reno\Cms\DTO\Settings\SettingForUpdate;
use Reno\Cms\Interfaces\Repositories\SettingRepositoryInterface;
use Reno\Cms\Models\Setting;

class SettingRepository implements SettingRepositoryInterface
{
    /**
     * @var array<int, array<string, Setting>>
     */
    private static array $contextSettingsCache = [];

    public function getByContext(int $contextId): Collection
    {
        return collect($this->getContextMap($contextId))->values();
    }

    public function findByKey(int $contextId, string $key): ?Setting
    {
        $map = $this->getContextMap($contextId);

        return $map[$key] ?? null;
    }

    public function getHomeResourceId(int $contextId): ?int
    {
        if ($contextId <= 0) {
            return null;
        }

        $setting = $this->findByKey($contextId, SettingRepositoryInterface::HOME_RESOURCE_SETTING_KEY);

        if ($setting === null) {
            return null;
        }

        $value = $setting->getTypedValue();

        if (!is_int($value) && !is_numeric($value)) {
            return null;
        }

        $resourceId = (int) $value;

        return $resourceId > 0 ? $resourceId : null;
    }

    public function updateOrCreate(SettingForUpdate $dto): Setting
    {
        $value = match ($dto->type) {
            'integer' => (string) $dto->value,
            'boolean' => $dto->value ? '1' : '0',
            'json' => json_encode($dto->value),
            default => (string) $dto->value,
        };

        $setting = Setting::updateOrCreate(
            [
                'context_id' => $dto->contextId,
                'key' => $dto->key,
            ],
            [
                'value' => $value,
                'type' => $dto->type,
            ]
        );

        $this->clearContextCache($dto->contextId);

        return $setting;
    }

    public function updateMany(int $contextId, array $settings): Collection
    {
        $existing = collect($this->getContextMap($contextId));
        $result = collect();

        foreach ($settings as $key => $value) {
            $attributes = [
                'value' => is_scalar($value) ? (string) $value : json_encode($value),
                'type' => $this->detectType($value),
            ];
            $setting = $existing->get($key);

            if ($setting instanceof Setting) {
                $setting->update($attributes);
            } else {
                $setting = Setting::create([
                    'context_id' => $contextId,
                    'key' => $key,
                    ...$attributes,
                ]);
            }

            $result->push($setting);
        }

        $this->clearContextCache($contextId);

        return $result;
    }

    public function delete(int $id): bool
    {
        $setting = Setting::query()->find($id);

        if (!$setting instanceof Setting) {
            return false;
        }

        $contextId = $setting->context_id;
        $isDeleted = $setting->delete();

        if ($isDeleted) {
            $this->clearContextCache($contextId);
        }

        return $isDeleted;
    }

    private function detectType(mixed $value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_int($value)) {
            return 'integer';
        }

        if (is_array($value) || is_object($value)) {
            return 'json';
        }

        return 'string';
    }

    /**
     * @return array<string, Setting>
     */
    private function getContextMap(int $contextId): array
    {
        if (isset(self::$contextSettingsCache[$contextId])) {
            return self::$contextSettingsCache[$contextId];
        }

        $map = Setting::query()
            ->where('context_id', $contextId)
            ->get()
            ->keyBy('key')
            ->all();

        self::$contextSettingsCache[$contextId] = $map;

        return $map;
    }

    private function clearContextCache(int $contextId): void
    {
        unset(self::$contextSettingsCache[$contextId]);
    }
}
