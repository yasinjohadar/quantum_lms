<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * صلاحية تفعيل/تعطيل الدرس مباشرة دون المرور بمسار المراجعة الإلزامي —
     * لبعض المعلمين/المشرفين المستثنَين من القاعدة العامة (راجع User::isLessonContentUploader()).
     * لا تُربط بأي دور هنا — تُمنح فردياً عبر دور إضافي من لوحة الأدوار.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(
            ['name' => 'lesson-toggle-status', 'guard_name' => 'web'],
            ['description' => 'تفعيل/تعطيل الدرس مباشرة دون المرور بالمراجعة']
        );

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // لا نحذف صلاحية قد تكون مربوطة بأدوار.
    }
};
