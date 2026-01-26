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
        
        $supervisorPermissions = [
            // صلاحيات إدارة الصفوف
            'class-list', 'class-show', 'class-enrolled-students',
            
            // صلاحيات إدارة المواد
            'subject-list', 'subject-create', 'subject-edit', 'subject-delete', 'subject-show',
            'subject-enrolled-students',
            
            // صلاحيات إدارة أقسام المواد
            'subject-section-create', 'subject-section-edit', 'subject-section-delete',
            
            // صلاحيات إدارة الوحدات
            'unit-create', 'unit-edit', 'unit-delete', 'unit-questions',
            'unit-attach-questions', 'unit-detach-question', 'unit-available-questions',
            
            // صلاحيات إدارة الدروس
            'lesson-create', 'lesson-edit', 'lesson-delete', 'lesson-show',
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
            'quiz-get-subjects-by-class', 'quiz-get-units',
            
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
            
            // صلاحيات إدارة المكتبة
            'library-list', 'library-create', 'library-edit', 'library-delete',
            'library-show', 'library-preview', 'library-download', 'library-stats',
            'library-get-subjects-by-class',
            
            // صلاحيات التقارير والإحصائيات
            'report-view', 'report-export',
            
            // صلاحيات لوحة التحكم
            'dashboard-view',
        ];
        
        $supervisorRole->syncPermissions($supervisorPermissions);

        // دور المعلم (Teacher)
        $teacherRole = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        
        $teacherPermissions = [
            // صلاحيات عرض الصفوف والمواد
            'class-list', 'class-show',
            'subject-list', 'subject-show',
            
            // صلاحيات إدارة الوحدات
            'unit-create', 'unit-edit', 'unit-delete', 'unit-questions',
            'unit-attach-questions', 'unit-detach-question', 'unit-available-questions',
            
            // صلاحيات إدارة الدروس
            'lesson-create', 'lesson-edit', 'lesson-delete', 'lesson-show',
            'lesson-approve-review', 'lesson-reject-review',
            
            // صلاحيات إدارة مرفقات الدروس
            'lesson-attachment-create', 'lesson-attachment-edit', 'lesson-attachment-delete',
            
            // صلاحيات إدارة الأسئلة
            'question-list', 'question-create', 'question-edit', 'question-delete', 'question-show',
            'question-duplicate', 'question-toggle-status', 'question-upload-image',
            
            // صلاحيات إدارة الاختبارات
            'quiz-list', 'quiz-create', 'quiz-edit', 'quiz-delete', 'quiz-show',
            'quiz-questions', 'quiz-add-question', 'quiz-remove-question',
            'quiz-reorder-questions', 'quiz-update-question-points', 'quiz-duplicate',
            'quiz-toggle-publish', 'quiz-preview', 'quiz-results',
            'quiz-get-subjects-by-class', 'quiz-get-units',
            
            // صلاحيات تصحيح الاختبارات
            'quiz-attempt-list', 'quiz-attempt-show', 'quiz-attempt-grade',
            'quiz-attempt-save-grade', 'quiz-attempt-regrade',
            'quiz-attempt-needs-grading', 'quiz-attempt-statistics',
            'quiz-attempt-grade-with-ai', 'quiz-attempt-grade-multiple-with-ai',
            
            // صلاحيات عرض الطلاب (في مواده فقط)
            'enrollment-list', 'enrollment-show',
            'subject-enrolled-students',
            
            // صلاحيات إدارة المكتبة (إضافة وتعديل فقط)
            'library-list', 'library-create', 'library-edit', 'library-show',
            'library-preview', 'library-download',
            
            // صلاحيات التقارير (في مواده فقط)
            'report-view',
            
            // صلاحيات لوحة التحكم
            'dashboard-view',
        ];
        
        $teacherRole->syncPermissions($teacherPermissions);
    }
}
