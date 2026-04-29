<?php

namespace Reno\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Reno\Cms\Helpers\TablePrefixHelper;

/**
 * @property int $id
 * @property int $resource_id
 * @property array<array-key, mixed> $data
 * @property array<array-key, mixed> $values
 * @property string|null $comment
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \Reno\Cms\Models\Resource|null $resource
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceVersion whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceVersion whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceVersion whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceVersion whereResourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceVersion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceVersion whereValues($value)
 * @mixin \Eloquent
 */
class ResourceVersion extends Model
{
    public static function getTableName(): string
    {
        return TablePrefixHelper::table('resource_versions');
    }

    public function getTable(): string
    {
        return static::getTableName();
    }

    protected $fillable = [
        'resource_id',
        'data',
        'values',
        'comment',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'values' => 'array',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}

