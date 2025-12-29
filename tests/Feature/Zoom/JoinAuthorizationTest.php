<?php

use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\Subject;
use App\Models\User;
use App\Models\ZoomJoinToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'teacher']);
    Role::firstOrCreate(['name' => 'student']);
});

test('student not enrolled cannot get join token', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    
    $subject = Subject::factory()->create();
    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
        'scheduled_at' => now()->addHour(),
    ]);

    $response = $this->actingAs($student)
        ->postJson(route('student.live-sessions.zoom.join-token', $liveSession->id));

    $response->assertStatus(403);
    $response->assertJson([
        'success' => false,
    ]);
});

test('student enrolled but outside time window cannot get token', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    
    $subject = Subject::factory()->create();
    Enrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
        'scheduled_at' => now()->addDays(2), // Too far in future
    ]);

    $response = $this->actingAs($student)
        ->postJson(route('student.live-sessions.zoom.join-token', $liveSession->id));

    $response->assertStatus(403);
});

test('token expires and cannot be reused', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    
    $subject = Subject::factory()->create();
    Enrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
        'scheduled_at' => now()->addMinutes(5),
    ]);

    $token = ZoomJoinToken::factory()->create([
        'user_id' => $student->id,
        'live_session_id' => $liveSession->id,
        'expires_at' => now()->subMinute(), // Expired
    ]);

    $this->assertFalse($token->isValid());
});

test('duplicate join token requests are rate limited', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    
    $subject = Subject::factory()->create();
    Enrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
        'scheduled_at' => now()->addMinutes(5),
    ]);

    // Make multiple requests quickly
    for ($i = 0; $i < 15; $i++) {
        $response = $this->actingAs($student)
            ->postJson(route('student.live-sessions.zoom.join-token', $liveSession->id));
    }

    // Should be rate limited after 10 requests (per config)
    $response->assertStatus(429);
});

test('device binding works correctly', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    
    $subject = Subject::factory()->create();
    Enrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
        'scheduled_at' => now()->addMinutes(5),
    ]);

    config(['zoom.security.enable_device_binding' => true]);

    $request1 = $this->actingAs($student)
        ->withHeaders(['User-Agent' => 'Test Browser 1'])
        ->postJson(route('student.live-sessions.zoom.join-token', $liveSession->id));

    $request2 = $this->actingAs($student)
        ->withHeaders(['User-Agent' => 'Test Browser 2'])
        ->postJson(route('student.live-sessions.zoom.join-token', $liveSession->id));

    // Should create separate tokens for different user agents
    $this->assertNotEquals(
        $request1->json('data.signature'),
        $request2->json('data.signature')
    );
});
