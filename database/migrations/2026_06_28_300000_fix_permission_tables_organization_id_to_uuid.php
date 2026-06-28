<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! config('permission.teams')) {
            return;
        }

        if (! in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $teamForeignKey = config('permission.column_names.team_foreign_key');
        $tableNames = config('permission.table_names');

        if (! Schema::hasTable($tableNames['roles'])) {
            return;
        }

        $rolesColumn = $this->columnType($tableNames['roles'], $teamForeignKey);

        if ($rolesColumn === null || $this->isUuidColumn($rolesColumn)) {
            return;
        }

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table): void {
            $table->dropPrimary('model_has_roles_role_model_type_primary');
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table): void {
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey): void {
            $table->dropUnique('roles_'.$teamForeignKey.'_name_guard_name_unique');
            $table->dropIndex('roles_team_foreign_key_index');
        });

        DB::statement(sprintf(
            'ALTER TABLE `%s` MODIFY `%s` CHAR(36) NULL',
            $tableNames['roles'],
            $teamForeignKey,
        ));

        DB::statement(sprintf(
            'ALTER TABLE `%s` MODIFY `%s` CHAR(36) NOT NULL',
            $tableNames['model_has_roles'],
            $teamForeignKey,
        ));

        DB::statement(sprintf(
            'ALTER TABLE `%s` MODIFY `%s` CHAR(36) NOT NULL',
            $tableNames['model_has_permissions'],
            $teamForeignKey,
        ));

        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey): void {
            $table->unique([$teamForeignKey, 'name', 'guard_name']);
            $table->index($teamForeignKey, 'roles_team_foreign_key_index');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey): void {
            $table->primary(
                [$teamForeignKey, 'role_id', 'model_id', 'model_type'],
                'model_has_roles_role_model_type_primary',
            );
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey): void {
            $table->primary(
                [$teamForeignKey, 'permission_id', 'model_id', 'model_type'],
                'model_has_permissions_permission_model_type_primary',
            );
        });
    }

    public function down(): void
    {
        if (! config('permission.teams')) {
            return;
        }

        if (! in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $teamForeignKey = config('permission.column_names.team_foreign_key');
        $tableNames = config('permission.table_names');

        if (! Schema::hasTable($tableNames['roles'])) {
            return;
        }

        $rolesColumn = $this->columnType($tableNames['roles'], $teamForeignKey);

        if ($rolesColumn === null || ! $this->isUuidColumn($rolesColumn)) {
            return;
        }

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table): void {
            $table->dropPrimary('model_has_roles_role_model_type_primary');
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table): void {
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey): void {
            $table->dropUnique('roles_'.$teamForeignKey.'_name_guard_name_unique');
            $table->dropIndex('roles_team_foreign_key_index');
        });

        DB::statement(sprintf(
            'ALTER TABLE `%s` MODIFY `%s` BIGINT UNSIGNED NULL',
            $tableNames['roles'],
            $teamForeignKey,
        ));

        DB::statement(sprintf(
            'ALTER TABLE `%s` MODIFY `%s` BIGINT UNSIGNED NOT NULL',
            $tableNames['model_has_roles'],
            $teamForeignKey,
        ));

        DB::statement(sprintf(
            'ALTER TABLE `%s` MODIFY `%s` BIGINT UNSIGNED NOT NULL',
            $tableNames['model_has_permissions'],
            $teamForeignKey,
        ));

        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey): void {
            $table->unique([$teamForeignKey, 'name', 'guard_name']);
            $table->index($teamForeignKey, 'roles_team_foreign_key_index');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey): void {
            $table->primary(
                [$teamForeignKey, 'role_id', 'model_id', 'model_type'],
                'model_has_roles_role_model_type_primary',
            );
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey): void {
            $table->primary(
                [$teamForeignKey, 'permission_id', 'model_id', 'model_type'],
                'model_has_permissions_permission_model_type_primary',
            );
        });
    }

    private function columnType(string $table, string $column): ?object
    {
        $result = DB::selectOne(
            'SELECT DATA_TYPE, COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column],
        );

        return $result;
    }

    private function isUuidColumn(object $column): bool
    {
        return in_array(strtolower($column->DATA_TYPE), ['char', 'uuid'], true)
            || str_contains(strtolower($column->COLUMN_TYPE), 'char(36)');
    }
};
