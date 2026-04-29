<?php

namespace Reno\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Reno\Cms\Helpers\TablePrefixHelper;

/**
 * @property int $id
 * @property int $resource_id
 * @property int $resource_field_id
 * @property string|null $value
 * @property int|null $media_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Reno\Cms\Models\Media|null $media
 * @property-read \Reno\Cms\Models\Resource $resource
 * @property-read \Reno\Cms\Models\ResourceField $resourceField
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceValue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceValue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceValue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceValue whereMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceValue whereResourceFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceValue whereResourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceValue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceValue whereValue($value)
 * @mixin \Eloquent
 */
class ResourceValue extends Model
{
    public static function getTableName(): string
    {
        return TablePrefixHelper::table('resource_values');
    }

    public function getTable(): string
    {
        return static::getTableName();
    }

    protected $fillable = [
        'resource_id',
        'resource_field_id',
        'value',
        'media_id',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function resourceField(): BelongsTo
    {
        return $this->belongsTo(ResourceField::class, 'resource_field_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}

