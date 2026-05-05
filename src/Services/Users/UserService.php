<?php

namespace Reno\Cms\Services\Users;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Reno\Cms\DTO\Users\UserForCreate;
use Reno\Cms\DTO\Users\UserForEdit;
use Reno\Cms\Interfaces\Services\UserServiceInterface;
use Reno\Cms\Models\CmsUser;

class UserService implements UserServiceInterface
{
    public function getAll(): Collection
    {
        return CmsUser::with('roles')->get();
    }

    public function findById(int $id): ?CmsUser
    {
        return CmsUser::with('roles')->find($id);
    }

    public function create(UserForCreate $dto): CmsUser
    {
        $user = CmsUser::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);

        if (!empty($dto->roles)) {
            $user->roles()->sync($dto->roles);
        }

        $user->load('roles');

        return $user;
    }

    public function update(int $id, UserForEdit $dto): CmsUser
    {
        $user = CmsUser::findOrFail($id);

        $data = [
            'name' => $dto->name,
            'email' => $dto->email,
        ];

        if ($dto->password !== null) {
            $data['password'] = Hash::make($dto->password);
        }

        $user->update($data);

        $user->roles()->sync($dto->roles);

        $user->load('roles');

        return $user;
    }

    public function delete(int $id): bool
    {
        $user = CmsUser::findOrFail($id);
        return $user->delete();
    }
}
