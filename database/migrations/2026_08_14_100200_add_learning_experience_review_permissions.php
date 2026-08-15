<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * إضافة صلاحيات مراجعة الاختبارات التفاعلية إن لم تكن موجودة مسبقاً.
     * لا يعدّل الأدوار ولا يربط صلاحيات بأي دور — الربط يبقى من لوحة الأدوار عندكم.
     *
     * @var array<int, array{name: string, description: string}>
     */
    private array $reviewPermissions = [
        ['name' => 'learning-experience-submit-for-review', 'description' => 'إرسال الاختبار التفاعلي للمراجعة'],
        ['name' => 'learning-experience-approve-review', 'description' => 'الموافقة على نشر الاختبار التفاعلي'],
        ['name' => 'learning-experience-reject-review', 'description' => 'رفض نشر الاختبار التفاعلي'],
        ['name' => 'review-queue-learning-experiences', 'description' => 'عرض الاختبارات التفاعلية قيد المراجعة'],
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
