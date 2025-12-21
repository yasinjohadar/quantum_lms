<?php

namespace Database\Seeders;

use App\Models\Reward;
use Illuminate\Database\Seeder;

class RewardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rewards = [
            [
                'name' => 'شهادة إكمال كورس',
                'description' => 'شهادة PDF لإكمال كورس كامل',
                'type' => 'certificate',
                'points_cost' => 500,
                'quantity_available' => null, // غير محدود
                'is_active' => true,
            ],
            [
                'name' => 'خصم 10%',
                'description' => 'خصم 10% على الكورس التالي',
                'type' => 'discount',
                'points_cost' => 300,
                'quantity_available' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'شارة خاصة',
                'description' => 'شارة حصرية للمستخدمين المميزين',
                'type' => 'badge',
                'points_cost' => 200,
                'quantity_available' => 50,
                'is_active' => true,
            ],
            [
                'name' => '100 نقطة إضافية',
                'description' => 'مكافأة 100 نقطة إضافية',
                'type' => 'points',
                'points_cost' => 150,
                'quantity_available' => null,
                'is_active' => true,
            ],
            [
                'name' => 'وصول مميز',
                'description' => 'وصول إلى محتوى حصري',
                'type' => 'access',
                'points_cost' => 400,
                'quantity_available' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'شهادة تفوق',
                'description' => 'شهادة PDF للتفوق في الكورس',
                'type' => 'certificate',
                'points_cost' => 600,
                'quantity_available' => null,
                'is_active' => true,
            ],
            [
                'name' => 'خصم 20%',
                'description' => 'خصم 20% على الكورس التالي',
                'type' => 'discount',
                'points_cost' => 500,
                'quantity_available' => 50,
                'is_active' => true,
            ],
        ];

        foreach ($rewards as $reward) {
            Reward::create($reward);
        }
    }
}

