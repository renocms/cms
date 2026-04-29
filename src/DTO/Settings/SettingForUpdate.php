<?php

namespace Reno\Cms\DTO\Settings;

use Reno\Cms\Http\Requests\Settings\SettingsUpdateRequest;

class SettingForUpdate
{
    public function __construct(
        public readonly int $contextId,
        public readonly string $key,
        public readonly mixed $value,
        public readonly string $type = 'string'
    )
    {
    }

    /**
     * Создать DTO из валидированных данных запроса
     *
     * @param SettingsUpdateRequest $request
     * @return self
     */
    public static function createFromRequest(SettingsUpdateRequest $request): self
    {
        $data = $request->validated();

        return new self(
            contextId: (int) $data['context_id'],
            key: $data['key'],
            value: $data['value'],
            type: $data['type'] ?? 'string'
        );
    }
}
