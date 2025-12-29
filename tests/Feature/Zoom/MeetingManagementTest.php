<?php

use App\Models\LiveSession;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'teacher']);
    Role::firstOrCreate(['name' => 'student']);
});

test('teacher can create meeting', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');
    
    $subject = Subject::factory()->create();
    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
    ]);

    $response = $this->actingAs($teacher)
        ->postJson(route('admin.live-sessions.zoom.create', $liveSession->id), [
            'title' => 'Test Session',
            'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'duration_minutes' => 60,
            'timezone' => 'UTC',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('zoom_meetings', [
        'live_session_id' => $liveSession->id,
    ]);
});

test('teacher can update meeting', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');
    
    $subject = Subject::factory()->create();
    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
    ]);

    $zoomMeeting = \App\Models\ZoomMeeting::factory()->create([
        'live_session_id' => $liveSession->id,
    ]);

    $response = $this->actingAs($teacher)
        ->postJson(route('admin.live-sessions.zoom.update', $liveSession->id), [
            'duration_minutes' => 90,
        ]);

    $response->assertRedirect();
    $liveSession->refresh();
    $this->assertEquals(90, $liveSession->duration_minutes);
});

test('teacher can cancel meeting', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');
    
    $subject = Subject::factory()->create();
    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
    ]);

    $zoomMeeting = \App\Models\ZoomMeeting::factory()->create([
        'live_session_id' => $liveSession->id,
    ]);

    $response = $this->actingAs($teacher)
        ->postJson(route('admin.live-sessions.zoom.cancel', $liveSession->id));

    $response->assertRedirect();
    $liveSession->refresh();
    $this->assertEquals('cancelled', $liveSession->status);
});

test('non-teacher cannot manage meetings', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    
    $subject = Subject::factory()->create();
    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
    ]);

    $response = $this->actingAs($student)
        ->postJson(route('admin.live-sessions.zoom.create', $liveSession->id));

    $response->assertStatus(403);
});
