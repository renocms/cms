<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Reno\Cms\Helpers\TablePrefixHelper;

return new class extends Migration
{
    public function up(): void
    {
        $rolesTable = TablePrefixHelper::table('roles');
        $permissionsTable = TablePrefixHelper::table('permissions');
        $rolePermissionTable = TablePrefixHelper::table('role_permission');
        $userRoleTable = TablePrefixHelper::table('user_role');

        $mediaTable = TablePrefixHelper::table('media');
        $contextsTable = TablePrefixHelper::table('contexts');

        $resourceTypesTable = TablePrefixHelper::table('resource_types');
        $resourceLayoutsTable = TablePrefixHelper::table('resource_layouts');

        $resourceFieldsTable = TablePrefixHelper::table('resource_fields');
        $resourceLayoutFieldsTable = TablePrefixHelper::table('resource_layout_fields');

        $resourcesTable = TablePrefixHelper::table('resources');
        $resourceValuesTable = TablePrefixHelper::table('resource_values');
        $resourceVersionsTable = TablePrefixHelper::table('resource_versions');

        $settingsTable = TablePrefixHelper::table('settings');

        Schema::create($rolesTable, function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create($permissionsTable, function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable(); // Название берется из языковых файлов
            $table->string('slug')->unique();
            $table->text('description')->nullable(); // Описание также может браться из языковых файлов
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create($rolePermissionTable, function (Blueprint $table) use ($rolesTable, $permissionsTable): void {
            $table->foreignId('role_id')->constrained($rolesTable)->onDelete('cascade');
            $table->foreignId('permission_id')->constrained($permissionsTable)->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create($userRoleTable, function (Blueprint $table) use ($rolesTable): void {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained($rolesTable)->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });

        Schema::create($contextsTable, function (Blueprint $table) use ($contextsTable): void {
            $table->id();
            $table->string('class')->nullable();
            $table->unique('class', $contextsTable . '_class_unique');
            $table->timestamps();
        });

        Schema::create($resourceTypesTable, function (Blueprint $table): void {
            $table->id();
            $table->string('class')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create($resourceLayoutsTable, function (Blueprint $table) use ($resourceTypesTable): void {
            $table->id();
            $table->foreignId('resource_type_id')->constrained($resourceTypesTable)->onDelete('cascade');
            $table->string('class')->nullable()->unique();
            $table->timestamp('class_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create($resourceFieldsTable, function (Blueprint $table) use ($resourceFieldsTable): void {
            $table->id();
            $table->string('key');
            $table->string('type');

            $table->unique(['key', 'type'], $resourceFieldsTable . '_key_type_unique');

            $table->timestamps();
        });

        Schema::create($resourceLayoutFieldsTable, function (Blueprint $table) use (
            $resourceLayoutsTable,
            $resourceFieldsTable
        ): void {
            $table->id();
            $table->foreignId('resource_layout_id')->constrained($resourceLayoutsTable)->onDelete('cascade');
            $table->foreignId('resource_field_id')->constrained($resourceFieldsTable)->onDelete('restrict');

            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['resource_layout_id', 'resource_field_id'], 'cms_rlf_layout_field_unique');
        });

        Schema::create($mediaTable, function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create($resourcesTable, function (Blueprint $table) use (
            $contextsTable,
            $resourcesTable,
            $resourceTypesTable,
            $resourceLayoutsTable,
        ): void {
            $table->id();

            $table->foreignId('context_id')->constrained($contextsTable)->onDelete('cascade');
            $table->foreignId('resource_type_id')->constrained($resourceTypesTable)->onDelete('restrict');
            $table->foreignId('resource_layout_id')->nullable()->constrained($resourceLayoutsTable)->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained($resourcesTable)->onDelete('cascade');

            $table->string('slug');
            $table->string('status')->default('draft');
            $table->integer('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();

            $table->foreignId('author_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('editor_id')->nullable()->constrained('users')->onDelete('set null');

            $table->boolean('is_folder')->default(false);
            $table->boolean('show_in_menu')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['context_id', 'parent_id', 'slug']);
            $table->index(['context_id', 'parent_id']);
        });

        Schema::create($resourceValuesTable, function (Blueprint $table) use (
            $resourcesTable,
            $resourceFieldsTable,
            $mediaTable
        ): void {
            $table->id();
            $table->foreignId('resource_id')->constrained($resourcesTable)->onDelete('cascade');
            $table->foreignId('resource_field_id')->constrained($resourceFieldsTable)->onDelete('restrict');
            $table->text('value')->nullable();
            $table->foreignId('media_id')->nullable()->constrained($mediaTable)->onDelete('set null');
            $table->timestamps();
        });

        Schema::create($resourceVersionsTable, function (Blueprint $table): void {
            $table->id();

            // Без внешнего ключа, чтобы история сохранялась после удаления ресурса
            $table->unsignedBigInteger('resource_id');
            $table->json('data');
            $table->json('values');
            $table->text('comment')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create($settingsTable, function (Blueprint $table) use ($contextsTable): void {
            $table->id();
            $table->foreignId('context_id')->constrained($contextsTable)->onDelete('cascade');
            $table->string('key');
            $table->longText('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->timestamps();

            $table->unique(['context_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(TablePrefixHelper::table('settings'));
        Schema::dropIfExists(TablePrefixHelper::table('resource_versions'));
        Schema::dropIfExists(TablePrefixHelper::table('resource_values'));
        Schema::dropIfExists(TablePrefixHelper::table('resource_layout_fields'));
        Schema::dropIfExists(TablePrefixHelper::table('resource_layouts'));
        Schema::dropIfExists(TablePrefixHelper::table('resources'));
        Schema::dropIfExists(TablePrefixHelper::table('resource_fields'));
        Schema::dropIfExists(TablePrefixHelper::table('resource_types'));
        Schema::dropIfExists(TablePrefixHelper::table('contexts'));
        Schema::dropIfExists(TablePrefixHelper::table('media'));

        Schema::dropIfExists(TablePrefixHelper::table('user_role'));
        Schema::dropIfExists(TablePrefixHelper::table('role_permission'));
        Schema::dropIfExists(TablePrefixHelper::table('permissions'));
        Schema::dropIfExists(TablePrefixHelper::table('roles'));
    }
};

