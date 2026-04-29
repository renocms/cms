<?php

namespace Reno\Cms\DTO\Users;

use Reno\Cms\Http\Requests\Users\UserStoreRequest;

class UserForCreate
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly array $roles = []
    )
    {
    }

    /**
     * Создать DTO из валидированных данных запроса
     *
     * @param UserStoreRequest $request
     * @return self
     */
    public static function createFromRequest(UserStoreRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            roles: $data['roles'] ?? []
        );
    }
}
