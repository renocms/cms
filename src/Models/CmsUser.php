<?php

namespace Reno\Cms\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Reno\Cms\Helpers\TablePrefixHelper;

class CmsUser extends User
{
    public function getTable(): string
    {
        return (new User())->getTable();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            TablePrefixHelper::table('user_role'),
            'user_id',
            'role_id'
        );
    }
}
