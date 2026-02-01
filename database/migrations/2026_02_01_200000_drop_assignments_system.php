<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove assignments (homework) system: drop related data and tables.
     * Keeps teacher-assignment and supervisor-assignment permissions (user role assignments).
     */
    public function up(): void
    {
        DB::table('review_comments')->where('reviewable_type', 'App\Models\Assignment')->delete();
        DB::table('event_reminders')->where('event_type', 'assignment')->delete();

        Schema::dropIfExists('assignment_grades');
        Schema::dropIfExists('assignment_submission_answers');
        Schema::dropIfExists('assignment_submission_files');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignment_questions');
        Schema::dropIfExists('assignments');

        $assignmentPermissionNames = [
            'review-queue-assignments',
            'assignment-approve-review',
            'assignment-reject-review',
            'assignment-submit-for-review',
            'assignment-list',
            'assignment-create',
            'assignment-edit',
            'assignment-delete',
            'assignment-show',
            'assignment-publish',
            'assignment-unpublish',
            'assignment-duplicate',
            'assignment-get-assignable-items',
        ];
        $permissionIds = DB::table('permissions')->whereIn('name', $assignmentPermissionNames)->pluck('id');
        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tables and permissions would need to be recreated via original migrations and seeders
    }
};
