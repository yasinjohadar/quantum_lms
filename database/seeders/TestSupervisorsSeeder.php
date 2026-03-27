<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestSupervisorsSeeder extends Seeder
{
    public function run(): void
    {
        $requiredRoles = [
            'supervisor',
            'supervisor-content-review',
            'supervisor-quiz-followup',
        ];

        foreach ($requiredRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Preset إضافي خاص بتجربة تخصيصات المشرف
        $assignmentRole = Role::firstOrCreate([
            'name' => 'supervisor-assignment-manager',
            'guard_name' => 'web',
        ]);
        $assignmentRole->syncPermissions([
            'supervisor-assignment-list',
            'supervisor-assignment-show',
            'supervisor-assignment-update',
            'supervisor-assignment-manage-classes',
            'supervisor-assignment-manage-subjects',
            'dashboard-view',
        ]);

        $password = Hash::make('123456789');

        $supervisors = [
            [
                'name' => 'مشرف مراجعة المحتوى',
                'email' => 'sup.review@example.com',
                'roles' => ['supervisor', 'supervisor-content-review'],
            ],
            [
                'name' => 'مشرف متابعة الاختبارات',
                'email' => 'sup.quiz@example.com',
                'roles' => ['supervisor', 'supervisor-quiz-followup'],
            ],
            [
                'name' => 'مشرف شامل',
                'email' => 'sup.combo@example.com',
                'roles' => ['supervisor', 'supervisor-content-review', 'supervisor-quiz-followup'],
            ],
            [
                'name' => 'مشرف تخصيصات',
                'email' => 'sup.assignments@example.com',
                'roles' => ['supervisor', 'supervisor-assignment-manager'],
            ],
            [
                'name' => 'مشرف أساسي',
                'email' => 'sup.basic@example.com',
                'roles' => ['supervisor'],
            ],
        ];

        foreach ($supervisors as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $password,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            $user->forceFill([
                'name' => $data['name'],
                'is_active' => true,
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();

            $user->syncRoles($data['roles']);
        }
    }
}
