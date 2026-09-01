<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * صلاحيات ميزة المكتبة المُعاد بناؤها. لا يعدّل الأدوار — الربط في RoleSeeder.
     *
     * @var array<int, array{name: string, description: string}>
     */
    private array $libraryPermissions = [
        ['name' => 'library-category-list', 'description' => 'عرض قائمة تصنيفات المكتبة'],
        ['name' => 'library-category-create', 'description' => 'إنشاء تصنيف مكتبة جديد'],
        ['name' => 'library-category-edit', 'description' => 'تعديل تصنيف المكتبة'],
        ['name' => 'library-category-delete', 'description' => 'حذف تصنيف المكتبة'],
        ['name' => 'library-item-list', 'description' => 'عرض قائمة عناصر المكتبة'],
        ['name' => 'library-item-create', 'description' => 'إنشاء عنصر مكتبة جديد'],
        ['name' => 'library-item-edit', 'description' => 'تعديل عنصر المكتبة'],
        ['name' => 'library-item-delete', 'description' => 'حذف عنصر المكتبة'],
        ['name' => 'library-item-show', 'description' => 'عرض تفاصيل عنصر المكتبة'],
        ['name' => 'library-item-download', 'description' => 'تحميل عنصر المكتبة (لوحة الإدارة)'],
    ];

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->libraryPermissions as $permission) {
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
