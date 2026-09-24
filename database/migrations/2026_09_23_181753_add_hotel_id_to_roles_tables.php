<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'hotel_id';

        // 1. Roles table
        if (!Schema::hasColumn($tableNames['roles'], $teamForeignKey)) {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey) {
                $table->unsignedBigInteger($teamForeignKey)->nullable()->after('id');
                $table->index($teamForeignKey, 'roles_team_foreign_key_index');
            });
            
            try {
                Schema::table($tableNames['roles'], function (Blueprint $table) {
                    $table->dropUnique('roles_name_guard_name_unique');
                });
            } catch (\Exception $e) {
                // Ignore if it's already dropped
            }

            try {
                Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey) {
                    $table->unique([$teamForeignKey, 'name', 'guard_name']);
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }

        // 2. model_has_permissions table
        if (!Schema::hasColumn($tableNames['model_has_permissions'], $teamForeignKey)) {
            try {
                Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames) {
                    $table->dropForeign($tableNames['model_has_permissions'].'_permission_id_foreign');
                });
            } catch (\Exception $e) {}

            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey, $columnNames) {
                $table->unsignedBigInteger($teamForeignKey)->nullable()->after($columnNames['model_morph_key']);
                $table->index($teamForeignKey, 'model_has_permissions_team_foreign_key_index');
            });

            try {
                // Raw SQL for dropping primary key since blueprint can be finicky
                DB::statement("ALTER TABLE `{$tableNames['model_has_permissions']}` DROP PRIMARY KEY");
            } catch (\Exception $e) {}

            try {
                Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey, $columnNames, $tableNames) {
                    $table->primary([$teamForeignKey, 'permission_id', $columnNames['model_morph_key'], 'model_type'],
                        'model_has_permissions_permission_model_type_primary');
                    $table->foreign('permission_id')
                        ->references('id')
                        ->on($tableNames['permissions'])
                        ->onDelete('cascade');
                });
            } catch (\Exception $e) {}
        }

        // 3. model_has_roles table
        if (!Schema::hasColumn($tableNames['model_has_roles'], $teamForeignKey)) {
            try {
                Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames) {
                    $table->dropForeign($tableNames['model_has_roles'].'_role_id_foreign');
                });
            } catch (\Exception $e) {}

            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey, $columnNames) {
                $table->unsignedBigInteger($teamForeignKey)->nullable()->after($columnNames['model_morph_key']);
                $table->index($teamForeignKey, 'model_has_roles_team_foreign_key_index');
            });

            try {
                DB::statement("ALTER TABLE `{$tableNames['model_has_roles']}` DROP PRIMARY KEY");
            } catch (\Exception $e) {}

            try {
                Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey, $columnNames, $tableNames) {
                    $table->primary([$teamForeignKey, 'role_id', $columnNames['model_morph_key'], 'model_type'],
                        'model_has_roles_role_model_type_primary');
                    $table->foreign('role_id')
                        ->references('id')
                        ->on($tableNames['roles'])
                        ->onDelete('cascade');
                });
            } catch (\Exception $e) {}
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
    }
};
