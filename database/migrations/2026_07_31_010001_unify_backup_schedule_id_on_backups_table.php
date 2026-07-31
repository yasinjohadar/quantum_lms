<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1: unify schedule FK on backups to backup_schedule_id.
 *
 * Some environments may have schedule_id (from an early migration that
 * ran before create_backups), backup_schedule_id (from create_backups),
 * or both. Canonical column is backup_schedule_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('backups')) {
            return;
        }

        $hasScheduleId = Schema::hasColumn('backups', 'schedule_id');
        $hasBackupScheduleId = Schema::hasColumn('backups', 'backup_schedule_id');

        if (! $hasBackupScheduleId) {
            Schema::table('backups', function (Blueprint $table) {
                $table->unsignedBigInteger('backup_schedule_id')->nullable()->after('created_by');
            });
            $hasBackupScheduleId = true;
        }

        if ($hasScheduleId && $hasBackupScheduleId) {
            // Copy any linked schedule ids that were written to the wrong column.
            DB::statement(
                'UPDATE backups
                 SET backup_schedule_id = schedule_id
                 WHERE schedule_id IS NOT NULL
                   AND backup_schedule_id IS NULL'
            );

            $this->dropForeignKeyIfExists('backups', 'schedule_id');

            Schema::table('backups', function (Blueprint $table) {
                $table->dropColumn('schedule_id');
            });
        }

        $this->ensureForeignKey('backups', 'backup_schedule_id', 'backup_schedules');
    }

    public function down(): void
    {
        if (! Schema::hasTable('backups')) {
            return;
        }

        // Recreate legacy column for rollback compatibility only.
        if (! Schema::hasColumn('backups', 'schedule_id')) {
            Schema::table('backups', function (Blueprint $table) {
                $table->unsignedBigInteger('schedule_id')->nullable()->after('created_by');
            });
        }

        if (Schema::hasColumn('backups', 'backup_schedule_id') && Schema::hasColumn('backups', 'schedule_id')) {
            DB::statement(
                'UPDATE backups
                 SET schedule_id = backup_schedule_id
                 WHERE backup_schedule_id IS NOT NULL
                   AND schedule_id IS NULL'
            );
        }
    }

    private function dropForeignKeyIfExists(string $table, string $column): void
    {
        $foreignKeys = $this->listForeignKeys($table, $column);

        foreach ($foreignKeys as $constraint) {
            Schema::table($table, function (Blueprint $blueprint) use ($constraint) {
                $blueprint->dropForeign($constraint);
            });
        }
    }

    private function ensureForeignKey(string $table, string $column, string $referencedTable): void
    {
        if (! Schema::hasTable($referencedTable) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        if (! empty($this->listForeignKeys($table, $column))) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $referencedTable) {
            $blueprint->foreign($column)
                ->references('id')
                ->on($referencedTable)
                ->nullOnDelete();
        });
    }

    /**
     * @return list<string>
     */
    private function listForeignKeys(string $table, string $column): array
    {
        $database = DB::getDatabaseName();

        $rows = DB::select(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        return collect($rows)
            ->pluck('CONSTRAINT_NAME')
            ->unique()
            ->values()
            ->all();
    }
};
