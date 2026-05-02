<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Reno\Cms\Helpers\TablePrefixHelper;

return new class extends Migration
{
    public function up(): void
    {
        $searchDataTable = TablePrefixHelper::table('search_data');
        $resourcesTable = TablePrefixHelper::table('resources');
        $driver = DB::getDriverName();

        Schema::create($searchDataTable, function (Blueprint $table) use ($resourcesTable): void {
            $table->id();
            $table->foreignId('resource_id')->unique()->constrained($resourcesTable)->onDelete('cascade');
            $table->foreignId('context_id')->index();
            $table->longText('search_text')->nullable();
            $table->timestamps();
        });

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table($searchDataTable, function (Blueprint $table): void {
                $table->fullText('search_text');
            });
        }

        if ($driver === 'pgsql') {
            DB::statement(
                sprintf(
                    "CREATE INDEX %s ON %s USING GIN (to_tsvector('simple', coalesce(search_text, '')))",
                    $searchDataTable . '_search_text_fts',
                    $searchDataTable,
                ),
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(TablePrefixHelper::table('search_data'));
    }
};
