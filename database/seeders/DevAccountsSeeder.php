<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\DevLogin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * حسابات الدخول السريع لبيئة التطوير.
 *
 * تُقرأ الحسابات من config/dev.php حتى تبقى مصدراً واحداً
 * لكل من الـ seeder وبوابة /dev/login وصفحة تسجيل الدخول.
 *
 * التشغيل: php artisan db:seed --class=DevAccountsSeeder
 */
class DevAccountsSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->warn('DevAccountsSeeder: تم التخطي — لا يعمل في بيئة الإنتاج.');

            return;
        }

        $dashboardTypes = [
            'admin' => 'admin',
            'supervisor' => 'admin',
            'teacher' => 'admin',
            'student' => 'student',
        ];

        foreach ((array) config('dev.accounts', []) as $account) {
            if (empty($account['email']) || empty($account['role'])) {
                continue;
            }

            $roleName = $account['role'];
            $password = $account['password'] ?? DevLogin::password();

            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            // ضبط نوع لوحة التحكم (مع حماية في حال عدم وجود العمود)
            if (isset($dashboardTypes[$roleName])) {
                try {
                    $role->update(['dashboard_type' => $dashboardTypes[$roleName]]);
                } catch (\Throwable $e) {
                    // تجاهل: العمود غير موجود بعد
                }
            }

            // دور المدير يحصل على جميع الصلاحيات
            if ($roleName === 'admin') {
                try {
                    $role->syncPermissions(Permission::all());
                } catch (\Throwable $e) {
                    // تجاهل: الصلاحيات لم تُنشأ بعد (شغّل PermissionSeeder)
                }
            }

            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'] ?? $account['email'],
                    'password' => Hash::make($password),
                ]
            );

            // نُعيد ضبط كلمة المرور والحالة في كل تشغيل حتى تبقى البيانات المعروضة صحيحة دائماً
            $user->forceFill([
                'name' => $account['name'] ?? $user->name,
                'password' => Hash::make($password),
                'is_active' => true,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);

            if (! empty($account['phone']) && empty($user->phone)) {
                $user->forceFill(['phone' => $account['phone']]);
            }

            $user->save();

            if (! $user->hasRole($roleName)) {
                $user->assignRole($role);
            }

            $this->info(sprintf('✔ %s — %s / %s', $account['label'] ?? $roleName, $account['email'], $password));
        }

        $this->info('بوابة الدخول السريع (بيئة التطوير فقط): ' . url('/dev/login'));
    }

    private function info(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }

    private function warn(string $message): void
    {
        if ($this->command) {
            $this->command->warn($message);
        }
    }
}
