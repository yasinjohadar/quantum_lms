<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            [
                'name' => 'مبتدئ',
                'level_number' => 1,
                'points_required' => 0,
                'icon' => 'fe fe-star',
                'color' => '#6c757d',
                'benefits' => ['access_basic_content'],
                'order' => 1,
            ],
            [
                'name' => 'متقدم',
                'level_number' => 2,
                'points_required' => 100,
                'icon' => 'fe fe-star',
                'color' => '#28a745',
                'benefits' => ['access_advanced_content', 'priority_support'],
                'order' => 2,
            ],
            [
                'name' => 'خبير',
                'level_number' => 3,
                'points_required' => 500,
                'icon' => 'fe fe-award',
                'color' => '#007bff',
                'benefits' => ['access_premium_content', 'priority_support', 'exclusive_badges'],
                'order' => 3,
            ],
            [
                'name' => 'أسطورة',
                'level_number' => 4,
                'points_required' => 1000,
                'icon' => 'fe fe-trophy',
                'color' => '#ffc107',
                'benefits' => ['access_all_content', 'priority_support', 'exclusive_badges', 'certificate'],
                'order' => 4,
            ],
            [
                'name' => 'ماستر',
                'level_number' => 5,
                'points_required' => 2500,
                'icon' => 'fe fe-crown',
                'color' => '#dc3545',
                'benefits' => ['access_all_content', 'priority_support', 'exclusive_badges', 'certificate', 'mentor_access'],
                'order' => 5,
            ],
            [
                'name' => 'أسطوري',
                'level_number' => 6,
                'points_required' => 5000,
                'icon' => 'fe fe-zap',
                'color' => '#6f42c1',
                'benefits' => ['access_all_content', 'priority_support', 'exclusive_badges', 'certificate', 'mentor_access', 'special_recognition'],
                'order' => 6,
            ],
            [
                'name' => 'إلهي',
                'level_number' => 7,
                'points_required' => 10000,
                'icon' => 'fe fe-shield',
                'color' => '#e83e8c',
                'benefits' => ['access_all_content', 'priority_support', 'exclusive_badges', 'certificate', 'mentor_access', 'special_recognition', 'admin_features'],
                'order' => 7,
            ],
        ];

        foreach ($levels as $level) {
            Level::create($level);
        }
    }
}

