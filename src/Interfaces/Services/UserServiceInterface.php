<?php

namespace Reno\Cms\Interfaces\Services;

use Illuminate\Support\Collection;
use Reno\Cms\DTO\Users\UserForCreate;
use Reno\Cms\DTO\Users\UserForEdit;
use Reno\Cms\Models\CmsUser;

interface UserServiceInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?CmsUser;

    public function create(UserForCreate $dto): CmsUser;

    public function update(int $id, UserForEdit $dto): CmsUser;

    public function delete(int $id): bool;
}

