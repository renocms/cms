<?php

namespace Reno\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Reno\Cms\Helpers\TablePrefixHelper;

/**
 * @property int $id
 * @property string $class
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Context newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Context newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Context query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Context whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Context whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Context whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Context whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Context extends Model
{
    public static function getTableName(): string
    {
        return TablePrefixHelper::table('contexts');
    }

    public function getTable(): string
    {
        return static::getTableName();
    }

    protected $fillable = [
        'class',
    ];
}
