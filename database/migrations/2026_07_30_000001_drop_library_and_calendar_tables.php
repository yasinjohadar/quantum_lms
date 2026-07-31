<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('event_reminders');
        Schema::dropIfExists('calendar_notes');
        Schema::dropIfExists('calendar_events');

        Schema::dropIfExists('library_favorites');
        Schema::dropIfExists('library_ratings');
        Schema::dropIfExists('library_views');
        Schema::dropIfExists('library_downloads');
        Schema::dropIfExists('library_item_tags');
        Schema::dropIfExists('library_items');
        Schema::dropIfExists('library_tags');
        Schema::dropIfExists('library_categories');

        Schema::enableForeignKeyConstraints();

        $permissionNames = [
            'library-list', 'library-create', 'library-edit', 'library-delete',
            'library-show', 'library-preview', 'library-download', 'library-stats',
            'library-get-subjects-by-class', 'library-dashboard-view', 'library-report-list',
            'calendar-list', 'calendar-create', 'calendar-edit', 'calendar-delete', 'calendar-get-events',
            'reminder-list', 'reminder-create', 'reminder-edit', 'reminder-delete',
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $permissionNames)
            ->orWhere('name', 'like', 'library-%')
            ->orWhere('name', 'like', 'calendar-%')
            ->orWhere('name', 'like', 'reminder-%')
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }
    }

    public function down(): void
    {
        // جداول المكتبة والتقويم أُزيلت نهائياً — لا يوجد rollback.
    }
};
