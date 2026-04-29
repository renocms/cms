<?php

namespace Reno\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Reno\Cms\Helpers\TablePrefixHelper;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $group
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Reno\Cms\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Permission extends Model
{
    public static function getTableName(): string
    {
        return TablePrefixHelper::table('permissions');
    }

    public function getTable(): string
    {
        return static::getTableName();
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'group',
    ];

    /**
     * Получить переведенное название разрешения
     *
     * @param string|null $value
     * @return string
     */
    public function getNameAttribute(?string $value): string
    {
        // Всегда получаем перевод из языкового файла по slug (не используем значение из базы)
        $slug = $this->attributes['slug'] ?? '';
        
        // Формируем ключ перевода: resources.view -> permission_resources_view (заменяем точки и дефисы на подчеркивания)
        $translationKey = 'permission_' . str_replace(['.', '-'], '_', $slug);
        $translated = trans("cms::cms.{$translationKey}");

        // Если перевод не найден, возвращаем slug
        return $translated !== "cms::cms.{$translationKey}" ? $translated : $slug;
    }

    /**
     * Получить переведенное описание разрешения
     *
     * @param string|null $value
     * @return string|null
     */
    public function getDescriptionAttribute(?string $value): ?string
    {
        // Если в базе есть значение, возвращаем его
        if (isset($this->attributes['description']) && $this->attributes['description'] !== null && $this->attributes['description'] !== '') {
            return $this->attributes['description'];
        }

        // Иначе пытаемся получить перевод из языкового файла
        $slug = $this->attributes['slug'] ?? '';
        $translationKey = 'permission_' . str_replace('.', '_', $slug) . '_description';
        $translated = trans("cms::cms.{$translationKey}");

        // Если перевод не найден, возвращаем null
        return $translated !== "cms::cms.{$translationKey}" ? $translated : null;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            TablePrefixHelper::table('role_permission'),
            'permission_id',
            'role_id'
        );
    }
}

