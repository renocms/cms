<?php

namespace Reno\Cms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthenticatableUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Reno\Cms\Helpers\TablePrefixHelper;
use Reno\Cms\Models\Permission;
use Reno\Cms\Models\Role;

class InstallCms extends Command
{
    private const array CORE_PERMISSIONS = [
        ['slug' => 'resources.view', 'group' => 'resources'],
        ['slug' => 'resources.create', 'group' => 'resources'],
        ['slug' => 'resources.edit', 'group' => 'resources'],
        ['slug' => 'resources.delete', 'group' => 'resources'],
        ['slug' => 'resources.publish', 'group' => 'resources'],
        ['slug' => 'resources.reorder', 'group' => 'resources'],
        ['slug' => 'resource_layouts.view', 'group' => 'resources'],
        ['slug' => 'resource_layouts.create', 'group' => 'resources'],
        ['slug' => 'resource_layouts.edit', 'group' => 'resources'],
        ['slug' => 'resource_layouts.delete', 'group' => 'resources'],
        ['slug' => 'resource_fields.view', 'group' => 'resources'],
        ['slug' => 'resource_fields.create', 'group' => 'resources'],
        ['slug' => 'resource_fields.edit', 'group' => 'resources'],
        ['slug' => 'resource_fields.delete', 'group' => 'resources'],
        ['slug' => 'resource_types.manage', 'group' => 'resources'],
        ['slug' => 'users.view', 'group' => 'users'],
        ['slug' => 'users.create', 'group' => 'users'],
        ['slug' => 'users.edit', 'group' => 'users'],
        ['slug' => 'users.delete', 'group' => 'users'],
        ['slug' => 'roles.view', 'group' => 'users'],
        ['slug' => 'roles.create', 'group' => 'users'],
        ['slug' => 'roles.edit', 'group' => 'users'],
        ['slug' => 'roles.delete', 'group' => 'users'],
        ['slug' => 'roles.manage', 'group' => 'users'],
        ['slug' => 'permissions.view', 'group' => 'users'],
        ['slug' => 'permissions.manage', 'group' => 'users'],
        ['slug' => 'settings.manage', 'group' => 'settings'],
        ['slug' => 'media.view', 'group' => 'media'],
        ['slug' => 'media.create', 'group' => 'media'],
        ['slug' => 'media.edit', 'group' => 'media'],
        ['slug' => 'media.delete', 'group' => 'media'],
    ];

    protected $signature = 'cms:install
        {--table-prefix= : Table prefix for CMS tables}
        {--admin-prefix= : Admin panel URL prefix}
        {--admin-email= : Email for super-admin user}
        {--without-user : Skip creating super-admin user}
        {--force : Run without interactive confirmation}';

    protected $description = 'Install and initialize Reno CMS';

    public function handle(): int
    {
        $this->info('Starting Reno CMS installation...');

        $existingTablePrefix = trim((string) config('cms.table_prefix'));
        $isAlreadyInstalled = $this->isAlreadyInstalled($existingTablePrefix);

        if ($isAlreadyInstalled) {
            $tablePrefix = $existingTablePrefix;
            $adminPrefix = $this->resolveExistingAdminPrefix();
            $this->line('Detected existing CMS installation. Reusing configured prefixes.');
        } else {
            $tablePrefix = $this->resolveTablePrefix();
            $adminPrefix = $this->resolveAdminPrefix();
        }

        $this->persistEnvironmentValue('CMS_TABLE_PREFIX', $tablePrefix);
        $this->persistEnvironmentValue('CMS_ADMIN_PREFIX', $adminPrefix);
        config([
            'cms.table_prefix' => $tablePrefix,
            'cms.admin_prefix' => $adminPrefix,
        ]);

        $this->line("CMS_TABLE_PREFIX: {$tablePrefix}");
        $this->line("CMS_ADMIN_PREFIX: {$adminPrefix}");

        if ($isAlreadyInstalled) {
            $this->line('Skipping migrations: resources table already exists for configured table prefix.');
        } else {
            if ($this->call('migrate', ['--force' => true]) !== self::SUCCESS) {
                $this->error('Migrations failed.');

                return self::FAILURE;
            }
        }

        if (!$this->areCmsTablesAvailable()) {
            return self::FAILURE;
        }

        $permissions = $this->syncCorePermissions();
        $role = $this->syncSuperAdminRole();

        if (!$this->option('without-user')) {
            if (!$this->ensureSuperAdminUser($role)) {
                return self::FAILURE;
            }
        } else {
            $this->line('Skipping super-admin user creation due to --without-user option.');
        }

        if ($this->call('vendor:publish', ['--tag' => 'cms-config', '--force' => true]) !== self::SUCCESS) {
            $this->error('Config publishing failed.');

            return self::FAILURE;
        }

        if ($this->call('vendor:publish', ['--tag' => 'cms-assets', '--force' => true]) !== self::SUCCESS) {
            $this->error('Assets publishing failed.');

            return self::FAILURE;
        }

        $this->generateMainContextStub();
        $this->generateDefaultLayoutStub();
        $this->generateDefaultViewStub();

        if (!$this->seedInitialCmsData()) {
            return self::FAILURE;
        }

        $this->info(sprintf('Core permissions synced: %d', $permissions));
        $this->info("Role '{$role->slug}' is synchronized with all available permissions.");
        $this->info('Reno CMS installation completed successfully.');

        return self::SUCCESS;
    }

    private function resolveTablePrefix(): string
    {
        $option = trim((string) $this->option('table-prefix'));

        if ($option !== '') {
            return $option;
        }

        $generated = $this->generateTablePrefix();

        if ($this->option('force')) {
            return $generated;
        }

        return trim((string) $this->ask('Enter table prefix for CMS tables', $generated));
    }

    private function resolveAdminPrefix(): string
    {
        $option = trim((string) $this->option('admin-prefix'));

        if ($option !== '') {
            return $option;
        }

        $generated = $this->generateAdminPrefix();

        if ($this->option('force')) {
            return $generated;
        }

        return trim((string) $this->ask('Enter admin panel URL prefix', $generated));
    }

    private function generateTablePrefix(): string
    {
        return 'cms_' . Str::lower(Str::random(6)) . '_';
    }

    private function generateAdminPrefix(): string
    {
        return 'admin-' . Str::lower(Str::random(4));
    }

    private function isAlreadyInstalled(string $existingTablePrefix): bool
    {
        if ($existingTablePrefix === '') {
            return false;
        }

        return Schema::hasTable($existingTablePrefix . 'resources');
    }

    private function resolveExistingAdminPrefix(): string
    {
        $configuredPrefix = trim((string) config('cms.admin_prefix'));

        if ($configuredPrefix !== '') {
            return $configuredPrefix;
        }

        return $this->generateAdminPrefix();
    }

    private function persistEnvironmentValue(string $key, string $value): void
    {
        $path = app()->environmentFilePath();
        $line = sprintf('%s="%s"', $key, addcslashes($value, '"'));

        if (!File::exists($path)) {
            File::put($path, $line . PHP_EOL);

            return;
        }

        $content = (string) File::get($path);
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        if (preg_match($pattern, $content) === 1) {
            $content = (string) preg_replace($pattern, $line, $content);
        } else {
            $content = rtrim($content) . PHP_EOL . $line . PHP_EOL;
        }

        File::put($path, $content);
    }

    private function syncCorePermissions(): int
    {
        foreach (self::CORE_PERMISSIONS as $permission) {
            Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                ['group' => $permission['group']],
            );
        }

        return count(self::CORE_PERMISSIONS);
    }

    private function areCmsTablesAvailable(): bool
    {
        if (
            !Schema::hasTable(Permission::getTableName())
            || !Schema::hasTable(Role::getTableName())
        ) {
            $this->error('CMS tables were not created for selected table prefix.');
            $this->line('Try using existing table prefix or run migrations on a clean database.');

            return false;
        }

        return true;
    }

    private function syncSuperAdminRole(): Role
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Administrator',
                'description' => 'Super Administrator',
            ],
        );

        if ($role->name !== 'Super Administrator') {
            $role->update(['name' => 'Super Administrator']);
        }

        $permissionIds = Permission::query()->pluck('id')->all();
        $role->permissions()->sync($permissionIds);

        return $role;
    }

    private function ensureSuperAdminUser(Role $role): bool
    {
        if ($this->superAdminUserExists($role)) {
            $this->line('Super-admin user already exists. Skipping user creation.');

            return true;
        }

        $adminEmail = trim((string) $this->option('admin-email'));

        if ($adminEmail === '') {
            if ($this->option('force')) {
                $this->error('Option --admin-email is required in --force mode unless --without-user is used.');

                return false;
            }

            $adminEmail = trim((string) $this->ask('Enter super-admin email'));
        }

        if ($adminEmail === '') {
            $this->error('Super-admin email must not be empty.');

            return false;
        }

        $password = Str::password(20);
        $user = $this->resolveUserModelClass()::query()->where('email', $adminEmail)->first();
        $wasCreated = false;

        if ($user === null) {
            $user = $this->resolveUserModelClass()::query()->create([
                'name' => 'Super Administrator',
                'email' => $adminEmail,
                'password' => Hash::make($password),
            ]);
            $wasCreated = true;
        }

        $this->attachRoleToUser($user, $role);

        if ($wasCreated) {
            $this->info('Super-admin user created.');
            $this->line("Email: {$adminEmail}");
            $this->line("Password: {$password}");
        } else {
            $this->line('Existing user found by email. Super-admin role attached.');
        }

        return true;
    }

    private function superAdminUserExists(Role $role): bool
    {
        return DB::table(TablePrefixHelper::table('user_role'))
            ->where('role_id', $role->id)
            ->exists();
    }

    private function attachRoleToUser(Model $user, Role $role): void
    {
        DB::table(TablePrefixHelper::table('user_role'))
            ->updateOrInsert([
                'user_id' => (int) $user->getKey(),
                'role_id' => $role->id,
            ]);
    }

    /**
     * @return class-string<Model&AuthenticatableUser>
     */
    private function resolveUserModelClass(): string
    {
        $modelClass = (string) config('auth.providers.users.model', \App\Models\User::class);

        if (!class_exists($modelClass) || !is_subclass_of($modelClass, AuthenticatableUser::class)) {
            throw new \RuntimeException("Configured user model '{$modelClass}' is invalid.");
        }

        return $modelClass;
    }

    private function generateDefaultLayoutStub(): void
    {
        $target = base_path('app/Reno/Layouts/DocumentDefaultLayout.php');
        $stub = $this->getStubPath('app/Reno/Layouts/DocumentDefaultLayout.php.stub');

        if (File::exists($target)) {
            $this->line("Layout already exists: {$target}");

            return;
        }

        $this->ensureDirectoryExists(dirname($target));
        File::put($target, File::get($stub));
        $this->line("Created layout: {$target}");
    }

    private function generateMainContextStub(): void
    {
        $target = base_path('app/Reno/Contexts/MainContext.php');
        $stub = $this->getStubPath('app/Reno/Contexts/MainContext.php.stub');

        if (File::exists($target)) {
            $this->line("Context already exists: {$target}");

            return;
        }

        $this->ensureDirectoryExists(dirname($target));
        File::put($target, File::get($stub));
        $this->line("Created context: {$target}");
    }

    private function generateDefaultViewStub(): void
    {
        $target = base_path('resources/views/web/pages/default.blade.php');
        $stub = $this->getStubPath('resources/views/web/pages/default.blade.php.stub');

        if (File::exists($target)) {
            $this->line("View already exists: {$target}");

            return;
        }

        $this->ensureDirectoryExists(dirname($target));
        File::put($target, File::get($stub));
        $this->line("Created view: {$target}");
    }

    private function seedInitialCmsData(): bool
    {
        $result = $this->call('db:seed', [
            '--class' => \Reno\Cms\Database\Seeders\InstallCmsSeeder::class,
            '--force' => true,
        ]);

        if ($result !== self::SUCCESS) {
            $this->error('Initial CMS data seeding failed.');

            return false;
        }

        return true;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    private function getStubPath(string $relativePath): string
    {
        return dirname(__DIR__, 3) . '/stubs/' . $relativePath;
    }
}
