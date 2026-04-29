<?php

namespace Reno\Cms\DTO\Roles;

use Reno\Cms\Http\Requests\Roles\RoleStoreRequest;

class RoleForCreate
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly array $permissions = []
    )
    {
    }

    /**
     * Создать DTO из валидированных данных запроса
     *
     * @param RoleStoreRequest $request
     * @return self
     */
    public static function createFromRequest(RoleStoreRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            permissions: $data['permissions'] ?? []
        );
    }
}
