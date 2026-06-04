<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
});

function createOpenDurationQuizAttempt(): array
{
    $user = User::factory()->create();

    $stage = Stage::create([
        'name' => 'Stage Open',
        'slug' => 'stage-open-'.uniqid(),
        'order' => 1,
        'is_active' => true,
    ]);

    $schoolClass = SchoolClass::create([
        'name' => 'Class Open',
        'slug' => 'class-open-'.uniqid(),
        'stage_id' => $stage->id,
        'order' => 1,
        'is_active' => true,
    ]);

    $subject = Subject::create([
        'name' => 'Subject Open',
        'slug' => 'subject-open-'.uniqid(),
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Open Duration Quiz',
        'duration_minutes' => null,
        'show_timer' => true,
        'auto_submit' => true,
        'is_active' => true,
        'is_published' => true,
        'pass_percentage' => 50,
        'grading_method' => 'highest',
    ]);

    $attempt = QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'attempt_number' => 1,
        'started_at' => now()->subMinutes(2),
        'status' => 'in_progress',
        'max_score' => 10,
    ]);

    return compact('user', 'quiz', 'attempt');
}

test('time api returns unlimited and elapsed for quiz without duration', function () {
    ['user' => $user, 'attempt' => $attempt] = createOpenDurationQuizAttempt();

    $response = $this->actingAs($user)
        ->getJson(route('student.quizzes.time', $attempt->id));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'unlimited' => true,
        ])
        ->assertJsonPath('timeout', null)
        ->assertJsonStructure(['elapsed', 'formatted_elapsed']);

    expect($response->json('elapsed'))->toBeGreaterThan(0);
});

test('time api does not time out in-progress open duration attempt', function () {
    ['user' => $user, 'attempt' => $attempt] = createOpenDurationQuizAttempt();

    $response = $this->actingAs($user)
        ->getJson(route('student.quizzes.time', $attempt->id));

    $response->assertOk();
    expect($response->json('timeout'))->not->toBeTrue();

    $attempt->refresh();
    expect($attempt->status)->toBe('in_progress');
});
