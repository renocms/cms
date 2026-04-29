<?php

namespace Reno\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Reno\Cms\Helpers\TablePrefixHelper;

/**
 * @property int $id
 * @property string|null $class
 * @property \Illuminate\Support\Carbon|null $class_modified_at
 * @property int $resource_type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Reno\Cms\Models\ResourceLayoutField> $layoutFields
 * @property-read int|null $layout_fields_count
 * @property-read \Reno\Cms\Models\ResourceType $resourceType
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayout newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayout newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayout query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayout whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayout whereClassModifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayout whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayout whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayout whereResourceTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayout whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ResourceLayout extends Model
{
    public static function getTableName(): string
    {
        return TablePrefixHelper::table('resource_layouts');
    }

    public function getTable(): string
    {
        return static::getTableName();
    }

    protected $fillable = [
        'resource_type_id',
        'class',
        'class_modified_at',
    ];

    protected $casts = [
        'class_modified_at' => 'datetime',
    ];

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function layoutFields(): HasMany
    {
        return $this->hasMany(ResourceLayoutField::class)->orderBy('sort_order');
    }
}

