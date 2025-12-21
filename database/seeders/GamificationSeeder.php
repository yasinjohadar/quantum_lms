<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GamificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            BadgesSeeder::class,
            AchievementsSeeder::class,
            LevelsSeeder::class,
            ChallengesSeeder::class,
            RewardsSeeder::class,
            CertificateTemplatesSeeder::class,
        ]);
    }
}

