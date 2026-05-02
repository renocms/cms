<?php

namespace Reno\Cms\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Auth\User as AuthenticatableUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Reno\Cms\Helpers\TablePrefixHelper;
use Reno\Cms\Interfaces\Repositories\SettingRepositoryInterface;
use Reno\Cms\Models\Context;
use Reno\Cms\Models\Resource;
use Reno\Cms\Models\ResourceField;
use Reno\Cms\Models\ResourceLayout;
use Reno\Cms\Models\ResourceType;
use Reno\Cms\Models\ResourceValue;
use Reno\Cms\Models\Role;
use Reno\Cms\Models\Setting;
use Reno\Cms\Resources\DocumentResourceType;
use RuntimeException;

class InstallCmsSeeder extends Seeder
{
    private const string MAIN_CONTEXT_CLASS = 'App\\Reno\\Contexts\\MainContext';
    private const string DEFAULT_LAYOUT_CLASS = 'App\\Reno\\Layouts\\DocumentDefaultLayout';
    private const string HOME_SLUG = 'home';

    public function run(): void
    {
        $context = Context::query()->updateOrCreate([
            'class' => self::MAIN_CONTEXT_CLASS,
        ]);

        $resourceType = ResourceType::query()->updateOrCreate([
            'class' => DocumentResourceType::class,
        ]);

        $resourceLayoutId = $this->resolveResourceLayoutId((int) $resourceType->getKey());
        $authorId = $this->resolveAuthorId();
        $homeResource = $this->resolveHomeResource($context, $resourceType, $resourceLayoutId, $authorId);

        $titleField = ResourceField::query()->updateOrCreate(
            ['key' => 'title', 'type' => 'string'],
        );
        $contentField = ResourceField::query()->updateOrCreate(
            ['key' => 'content', 'type' => 'rich-content'],
        );

        ResourceValue::query()->updateOrCreate(
            [
                'resource_id' => $homeResource->getKey(),
                'resource_field_id' => $titleField->getKey(),
            ],
            [
                'value' => __('cms::cms.install_homepage_title'),
            ],
        );

        ResourceValue::query()->updateOrCreate(
            [
                'resource_id' => $homeResource->getKey(),
                'resource_field_id' => $contentField->getKey(),
            ],
            [
                'value' => __('cms::cms.install_homepage_content'),
            ],
        );

        Setting::query()->updateOrCreate(
            [
                'context_id' => $context->getKey(),
                'key' => SettingRepositoryInterface::HOME_RESOURCE_SETTING_KEY,
            ],
            [
                'value' => (string) $homeResource->getKey(),
                'type' => 'integer',
            ],
        );
    }

    private function resolveResourceLayoutId(int $resourceTypeId): ?int
    {
        if (!class_exists(self::DEFAULT_LAYOUT_CLASS)) {
            return null;
        }

        $resourceLayout = ResourceLayout::query()->updateOrCreate(
            [
                'class' => self::DEFAULT_LAYOUT_CLASS,
            ],
            [
                'resource_type_id' => $resourceTypeId,
                'class_modified_at' => now(),
            ],
        );

        return (int) $resourceLayout->getKey();
    }

    private function resolveAuthorId(): int
    {
        $superAdminRole = Role::query()->where('slug', 'super-admin')->first();

        if ($superAdminRole !== null) {
            $superAdminUserId = DB::table(TablePrefixHelper::table('user_role'))
                ->where('role_id', $superAdminRole->getKey())
                ->value('user_id');

            if (is_numeric($superAdminUserId) && (int) $superAdminUserId > 0) {
                return (int) $superAdminUserId;
            }
        }

        $userModelClass = $this->resolveUserModelClass();
        $firstUserId = $userModelClass::query()->value('id');

        if (is_numeric($firstUserId) && (int) $firstUserId > 0) {
            return (int) $firstUserId;
        }

        throw new RuntimeException('Cannot create home resource without users. Create a user first or run cms:install with --admin-email.');
    }

    /**
     * @return class-string<Model&AuthenticatableUser>
     */
    private function resolveUserModelClass(): string
    {
        $modelClass = (string) config('auth.providers.users.model', \App\Models\User::class);

        if (!class_exists($modelClass) || !is_subclass_of($modelClass, AuthenticatableUser::class)) {
            throw new RuntimeException("Configured user model '{$modelClass}' is invalid.");
        }

        return $modelClass;
    }

    private function resolveHomeResource(
        Context $context,
        ResourceType $resourceType,
        ?int $resourceLayoutId,
        int $authorId,
    ): Resource {
        $existingSetting = Setting::query()
            ->where('context_id', $context->getKey())
            ->where('key', SettingRepositoryInterface::HOME_RESOURCE_SETTING_KEY)
            ->first();

        if ($existingSetting !== null && is_numeric($existingSetting->value)) {
            $resource = Resource::query()->find((int) $existingSetting->value);

            if ($resource !== null) {
                $resource->update([
                    'resource_type_id' => (int) $resourceType->getKey(),
                    'resource_layout_id' => $resourceLayoutId,
                    'author_id' => $authorId,
                    'editor_id' => $authorId,
                    'status' => 'published',
                    'published_at' => now(),
                    'show_in_menu' => true,
                    'is_folder' => false,
                ]);

                return $resource;
            }
        }

        $slug = $this->resolveUniqueHomeSlug((int) $context->getKey());

        return Resource::query()->create([
            'context_id' => (int) $context->getKey(),
            'resource_type_id' => (int) $resourceType->getKey(),
            'resource_layout_id' => $resourceLayoutId,
            'parent_id' => null,
            'slug' => $slug,
            'status' => 'published',
            'sort_order' => 0,
            'published_at' => now(),
            'author_id' => $authorId,
            'editor_id' => $authorId,
            'is_folder' => false,
            'show_in_menu' => true,
        ]);
    }

    private function resolveUniqueHomeSlug(int $contextId): string
    {
        $baseSlug = self::HOME_SLUG;
        $slug = $baseSlug;
        $suffix = 1;

        while (Resource::query()
            ->where('context_id', $contextId)
            ->whereNull('parent_id')
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . Str::lower(Str::random(4)) . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
