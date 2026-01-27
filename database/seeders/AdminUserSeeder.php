<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء أو جلب دور المدير
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // منح جميع الصلاحيات لدور المدير
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);

        // إنشاء أو جلب مستخدم مدير افتراضي
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]
        );

        // تعيين دور المدير للمستخدم
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
        }

        // إنشاء أو جلب دور مستخدم عادي
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // منح صلاحيات محدودة للمستخدم العادي
        $userPermissions = [
            'dashboard-view',
            'user-show', // يمكنه رؤية ملفه الشخصي فقط
        ];

        $userRole->syncPermissions($userPermissions);

        // إنشاء أو جلب دور المشرف
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);

        // إنشاء أو جلب مستخدم مشرف افتراضي
        $supervisorUser = User::firstOrCreate(
            ['email' => 'supervisor@example.com'],
            [
                'name' => 'مشرف النظام',
                'password' => Hash::make('123456789'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // تعيين دور المشرف للمستخدم
        if (!$supervisorUser->hasRole('supervisor')) {
            $supervisorUser->assignRole($supervisorRole);
        }

        // إنشاء أو جلب دور المعلم
        $teacherRole = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);

        // إنشاء أو جلب مستخدم معلم افتراضي
        $teacherUser = User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'معلم النظام',
                'password' => Hash::make('123456789'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // تعيين دور المعلم للمستخدم
        if (!$teacherUser->hasRole('teacher')) {
            $teacherUser->assignRole($teacherRole);
        }
    }
}
