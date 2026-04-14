<?php

use App\Models\PointTransaction;
use App\Models\Reward;
use App\Models\Role;
use App\Models\User;

test('admin gamification rules page renders', function () {
    $role = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    $user = User::factory()->create();
    $user->assignRole($role);

    $response = $this->actingAs($user)->get(route('admin.gamification.rules'));

    $response->assertOk();
    $response->assertSee('قواعد النقاط والأحداث', false);
});

test('student can claim reward when enough points', function () {
    $user = User::factory()->create();

    PointTransaction::create([
        'user_id' => $user->id,
        'type' => 'manual',
        'points' => 500,
        'source_type' => null,
        'source_id' => null,
        'metadata' => null,
    ]);

    $reward = Reward::create([
        'name' => 'Test Reward',
        'description' => null,
        'type' => 'points',
        'points_cost' => 50,
        'quantity_available' => null,
        'quantity_claimed' => 0,
        'is_active' => true,
        'metadata' => null,
    ]);

    $response = $this->actingAs($user)->post(route('student.gamification.rewards.claim', $reward));

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('user_rewards', [
        'user_id' => $user->id,
        'reward_id' => $reward->id,
    ]);
});
