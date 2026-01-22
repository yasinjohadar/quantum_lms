<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'SYP',
                'name' => 'ليرة سورية',
                'symbol' => 'ل.س',
                'is_default' => true,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'code' => 'USD',
                'name' => 'دولار أمريكي',
                'symbol' => '$',
                'is_default' => false,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'code' => 'TRY',
                'name' => 'ليرة تركية',
                'symbol' => '₺',
                'is_default' => false,
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
