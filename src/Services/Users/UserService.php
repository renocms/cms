<?php

namespace Reno\Cms\Services\Users;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Reno\Cms\DTO\Users\UserForCreate;
use Reno\Cms\DTO\Users\UserForEdit;
use Reno\Cms\Interfaces\Services\UserServiceInterface;

class UserService implements UserServiceInterface
{
    public function getAll(): Collection
    {
        return User::with('roles')->get();
    }

    public function findById(int $id): ?User
    {
        return User::with('roles')->find($id);
    }

    public function create(UserForCreate $dto): User
    {
        $user = User::create([
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

    public function update(int $id, UserForEdit $dto): User
    {
        $user = User::findOrFail($id);

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
        $user = User::findOrFail($id);
        return $user->delete();
    }
}
