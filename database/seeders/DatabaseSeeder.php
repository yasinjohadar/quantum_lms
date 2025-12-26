<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            AdminUserSeeder::class,
            StageSeeder::class,
            ClassSeeder::class,
            SubjectSeeder::class,
            StudentsSeeder::class,
            QuestionsSeeder::class,
            ReportTemplatesSeeder::class,
            SystemSettingsSeeder::class,
            GamificationSeeder::class,
            AIModelsSeeder::class,
            AnalyticsEventsSeeder::class,
        ]);

        // إنشاء مستخدم تجريبي إضافي (إن لم يكن موجوداً)
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'user',
                'password' => bcrypt('password'),
            ]
        );
    }
}

