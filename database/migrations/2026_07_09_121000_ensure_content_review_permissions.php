<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * إضافة صلاحيات مراجعة المحتوى إن لم تكن موجودة مسبقاً.
     * لا يعدّل الأدوار ولا يربط صلاحيات بأي دور — الربط يبقى من لوحة الأدوار عندكم.
     *
     * @var array<int, array{name: string, description: string}>
     */
    private array $reviewPermissions = [
        ['name' => 'lesson-submit-for-review', 'description' => 'إرسال الدرس للمراجعة'],
        ['name' => 'lesson-approve-review', 'description' => 'الموافقة على نشر الدرس'],
        ['name' => 'lesson-reject-review', 'description' => 'رفض نشر الدرس'],
        ['name' => 'quiz-submit-for-review', 'description' => 'إرسال الاختبار للمراجعة'],
        ['name' => 'quiz-approve-review', 'description' => 'الموافقة على نشر الاختبار'],
        ['name' => 'quiz-reject-review', 'description' => 'رفض نشر الاختبار'],
        ['name' => 'review-queue-list', 'description' => 'عرض قائمة المراجعة'],
        ['name' => 'review-queue-lessons', 'description' => 'عرض الدروس قيد المراجعة'],
        ['name' => 'review-queue-quizzes', 'description' => 'عرض الاختبارات قيد المراجعة'],
        ['name' => 'review-comment-create', 'description' => 'إنشاء ملاحظة مراجعة'],
        ['name' => 'review-comment-edit', 'description' => 'تعديل ملاحظة مراجعة'],
        ['name' => 'review-comment-delete', 'description' => 'حذف ملاحظة مراجعة'],
        ['name' => 'review-comment-reply', 'description' => 'الرد على ملاحظة مراجعة'],
        ['name' => 'review-comment-resolve', 'description' => 'حل/إلغاء حل ملاحظة مراجعة'],
    ];

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->reviewPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['description' => $permission['description']]
            );
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // لا نحذف صلاحيات قد تكون مربوطة بأدوار.
    }
};
