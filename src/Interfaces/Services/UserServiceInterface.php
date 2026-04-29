<?php

namespace Reno\Cms\Interfaces\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Reno\Cms\DTO\Users\UserForCreate;
use Reno\Cms\DTO\Users\UserForEdit;

interface UserServiceInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?User;

    public function create(UserForCreate $dto): User;

    public function update(int $id, UserForEdit $dto): User;

    public function delete(int $id): bool;
}

