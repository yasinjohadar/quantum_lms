<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // دور المشرف (Supervisor)
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        // تحديث dashboard_type لضمان القيمة الصحيحة (مع حماية في حال عدم وجود العمود)
        try {
            $supervisorRole->update(['dashboard_type' => 'admin']);
        } catch (\Exception $e) {
            // تجاهل الخطأ إذا كان عمود dashboard_type غير موجود بعد
        }
        
        $supervisorPermissions = [
            // صلاحيات إدارة الصفوف
            'class-list', 'class-show', 'class-enrolled-students', 'class-toggle-status',

            // صلاحيات إدارة المواد
            'subject-list', 'subject-create', 'subject-edit', 'subject-delete', 'subject-show',
            'subject-enrolled-students', 'subject-toggle-status',

            // صلاحيات إدارة أقسام المواد
            'subject-section-create', 'subject-section-edit', 'subject-section-delete',
            
            // صلاحيات إدارة الوحدات
            'unit-create', 'unit-edit', 'unit-delete', 'unit-questions',
            'unit-attach-questions', 'unit-detach-question', 'unit-available-questions',
            
            // صلاحيات إدارة الدروس
            'lesson-list', 'lesson-create', 'lesson-edit', 'lesson-delete', 'lesson-show',
            'lesson-approve-review', 'lesson-reject-review',
            
            // صلاحيات إدارة مرفقات الدروس
            'lesson-attachment-create', 'lesson-attachment-edit', 'lesson-attachment-delete',
            
            // صلاحيات إدارة الأسئلة
            'question-list', 'question-create', 'question-edit', 'question-delete', 'question-show',
            'question-duplicate', 'question-toggle-status', 'question-upload-image',
            'question-export', 'question-export-template', 'question-import', 'question-show-import',
            
            // صلاحيات إدارة الاختبارات
            'quiz-list', 'quiz-create', 'quiz-edit', 'quiz-delete', 'quiz-show',
            'quiz-questions', 'quiz-add-question', 'quiz-remove-question',
            'quiz-reorder-questions', 'quiz-update-question-points', 'quiz-duplicate',
            'quiz-toggle-publish', 'quiz-preview', 'quiz-results', 'quiz-export-results',
            'quiz-get-subjects-by-class', 'quiz-get-classes-by-stage', 'quiz-get-units',
            'quiz-approve-review', 'quiz-reject-review',
            
            // صلاحيات إدارة محاولات الاختبارات
            'quiz-attempt-list', 'quiz-attempt-show', 'quiz-attempt-grade',
            'quiz-attempt-save-grade', 'quiz-attempt-regrade', 'quiz-attempt-delete',
            'quiz-attempt-reset-user', 'quiz-attempt-needs-grading', 'quiz-attempt-statistics',
            'quiz-attempt-grade-with-ai', 'quiz-attempt-grade-multiple-with-ai',
            
            // صلاحيات إدارة التسجيلات
            'enrollment-list', 'enrollment-show', 'enrollment-create', 'enrollment-delete',
            'enrollment-pending-requests', 'enrollment-approve', 'enrollment-reject',
            'enrollment-approve-multiple', 'enrollment-reject-multiple',
            'enrollment-search-students', 'enrollment-get-subjects-by-class',
            'enrollment-class-pending-requests', 'enrollment-approve-class',
            'enrollment-reject-class', 'enrollment-approve-multiple-class',
            'enrollment-reject-multiple-class',
            
            // صلاحيات إدارة المدفوعات
            'payment-list', 'payment-show', 'payment-review', 'payment-approve',
            'payment-reject', 'payment-download-receipt',
            
            // صلاحيات إدارة المستخدمين (عرض فقط)
            'user-list', 'user-show', 'user-login-logs',
            
            // صلاحيات التقارير والإحصائيات
            'report-view', 'report-export',
            
            // صلاحيات المراجعة
            'review-queue-list',
            'review-queue-lessons',
            'review-queue-quizzes',

            // صلاحيات تخصيص المعلمين/المشرفين والمتابعة
            'teacher-assignment-list', 'teacher-assignment-show', 'teacher-assignment-update',
            'teacher-assignment-manage-classes', 'teacher-assignment-manage-subjects',
            'supervisor-assignment-list', 'supervisor-assignment-show', 'supervisor-assignment-update',
            'supervisor-assignment-manage-classes', 'supervisor-assignment-manage-subjects',
            'teacher-progress-view',
            'academic-year-list', 'academic-week-list',
            
            // صلاحيات لوحة التحكم
            'dashboard-view',
        ];
        
        $supervisorRole->syncPermissions($supervisorPermissions);

        // دور المعلم (Teacher)
        $teacherRole = Role::updateOrCreate(
            ['name' => 'teacher', 'guard_name' => 'web'],
            ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
        );
        
        $teacherPermissions = [
            // صلاحيات أساسية فقط (Read-only)
            'dashboard-view',
            'class-list', 'class-show',
            'subject-list', 'subject-show',
        ];
        
        $teacherRole->syncPermissions($teacherPermissions);

        // قوالب أدوار تشغيلية مرنة (يمكن دمجها مع أدوار أخرى)
        $supervisorContentReviewRole = Role::firstOrCreate([
            'name' => 'supervisor-content-review',
            'guard_name' => 'web',
        ]);
        $supervisorContentReviewRole->syncPermissions([
            'review-queue-list',
            'review-queue-lessons',
            'review-queue-quizzes',
            'lesson-list',
            'lesson-show',
            'quiz-list',
            'quiz-show',
            'lesson-approve-review',
            'lesson-reject-review',
            'quiz-approve-review',
            'quiz-reject-review',
            'report-view',
            'dashboard-view',
        ]);

        $supervisorQuizFollowupRole = Role::firstOrCreate([
            'name' => 'supervisor-quiz-followup',
            'guard_name' => 'web',
        ]);
        $supervisorQuizFollowupRole->syncPermissions([
            'quiz-attempt-list',
            'quiz-attempt-show',
            'quiz-attempt-needs-grading',
            'quiz-attempt-statistics',
            'quiz-results',
            'report-view',
            'dashboard-view',
        ]);

        $teacherContentUploaderRole = Role::updateOrCreate(
            ['name' => 'teacher-content-uploader', 'guard_name' => 'web'],
            ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
        );
        $teacherContentUploaderRole->syncPermissions([
            'unit-create',
            'unit-edit',
            'unit-delete',
            'unit-questions',
            'unit-attach-questions',
            'unit-detach-question',
            'unit-available-questions',
            'subject-section-create',
            'subject-section-edit',
            'subject-section-delete',
            'lesson-list',
            'lesson-create',
            'lesson-edit',
            'lesson-delete',
            'lesson-show',
            'lesson-submit-for-review',
            'lesson-attachment-create',
            'lesson-attachment-edit',
            'lesson-attachment-delete',
            'quiz-create',
            'quiz-edit',
            'quiz-delete',
            'quiz-show',
            'quiz-list',
            'quiz-questions',
            'quiz-add-question',
            'quiz-remove-question',
            'quiz-reorder-questions',
            'quiz-update-question-points',
            'quiz-duplicate',
            'quiz-toggle-publish',
            'quiz-preview',
            'quiz-get-subjects-by-class',
            'quiz-get-classes-by-stage',
            'quiz-get-units',
            'quiz-submit-for-review',
            'question-list',
            'question-create',
            'question-edit',
            'question-delete',
            'question-show',
            'question-duplicate',
            'question-toggle-status',
            'question-upload-image',
            'subject-enrolled-students',
            'dashboard-view',
        ]);

        $teacherAssistantRole = Role::updateOrCreate(
            ['name' => 'teacher-assistant', 'guard_name' => 'web'],
            ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
        );
        $teacherAssistantRole->syncPermissions([
            'class-list',
            'class-show',
            'subject-list',
            'subject-show',
            'unit-questions',
            'question-list',
            'question-show',
            'quiz-list',
            'quiz-show',
            'quiz-preview',
            'subject-enrolled-students',
            'enrollment-list',
            'enrollment-show',
            'report-view',
            'dashboard-view',
        ]);

        $teacherQuizFollowupRole = Role::updateOrCreate(
            ['name' => 'teacher-quiz-followup', 'guard_name' => 'web'],
            ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
        );
        $teacherQuizFollowupRole->syncPermissions([
            'quiz-attempt-list',
            'quiz-attempt-show',
            'quiz-attempt-grade',
            'quiz-attempt-save-grade',
            'quiz-attempt-regrade',
            'quiz-attempt-needs-grading',
            'quiz-attempt-statistics',
            'quiz-attempt-grade-with-ai',
            'quiz-attempt-grade-multiple-with-ai',
            'quiz-results',
            'report-view',
            'dashboard-view',
        ]);
        
        // تحديث الأدوار الموجودة لتحديد نوع الواجهة (مع حماية في حال عدم وجود العمود)
        try {
            Role::where('name', 'admin')->update(['dashboard_type' => 'admin']);
            Role::where('name', 'supervisor')->update(['dashboard_type' => 'admin']);
            Role::where('name', 'teacher')->update(['dashboard_type' => 'admin']);
            Role::where('name', 'student')->update(['dashboard_type' => 'student']);
        } catch (\Exception $e) {
            // تجاهل الخطأ إذا كان عمود dashboard_type غير موجود بعد
        }
    }
}
