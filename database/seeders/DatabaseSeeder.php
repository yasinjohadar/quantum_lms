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
            RoleSeeder::class,
            AdminUserSeeder::class,
            GamificationSettingsSeeder::class,
            GamificationSeeder::class,
            // StageSeeder::class,
            // ClassSeeder::class,
            // SubjectSeeder::class,
            // StudentsSeeder::class,
            // QuestionsSeeder::class,
            // ReportTemplatesSeeder::class, // Empty file
            // SystemSettingsSeeder::class, // Empty file
            // AIModelsSeeder::class, // Check if needed
            // AnalyticsEventsSeeder::class, // Check if needed
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

