<?php

namespace Reno\Cms\DTO\Users;

use Reno\Cms\Http\Requests\Users\UserUpdateRequest;

class UserForEdit
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password,
        public readonly array $roles = []
    )
    {
    }

    /**
     * Создать DTO из валидированных данных запроса
     *
     * @param UserUpdateRequest $request
     * @return self
     */
    public static function createFromRequest(UserUpdateRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'] ?? null,
            roles: $data['roles'] ?? []
        );
    }
}
