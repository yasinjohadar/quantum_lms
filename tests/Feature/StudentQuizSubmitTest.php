<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
});

function createStudentQuizAttempt(string $status = 'in_progress', ?int $durationMinutes = 30): array
{
    $user = User::factory()->create();

    $stage = Stage::create([
        'name' => 'Stage Test',
        'slug' => 'stage-test-'.uniqid(),
        'order' => 1,
        'is_active' => true,
    ]);

    $schoolClass = SchoolClass::create([
        'name' => 'Class Test',
        'slug' => 'class-test-'.uniqid(),
        'stage_id' => $stage->id,
        'order' => 1,
        'is_active' => true,
    ]);

    $subject = Subject::create([
        'name' => 'Subject Test',
        'slug' => 'subject-test-'.uniqid(),
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Quiz Test',
        'duration_minutes' => $durationMinutes,
        'show_timer' => true,
        'is_active' => true,
        'is_published' => true,
    ]);

    $attempt = QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'attempt_number' => 1,
        'started_at' => now()->subMinutes(($durationMinutes ?? 30) + 1),
        'status' => $status,
        'max_score' => 10,
    ]);

    return compact('user', 'quiz', 'attempt');
}

test('submitting a timed out attempt returns json redirect to result page', function () {
    ['user' => $user, 'quiz' => $quiz, 'attempt' => $attempt] = createStudentQuizAttempt('timed_out');

    $response = $this->actingAs($user)
        ->postJson(route('student.quizzes.submit', $attempt->id));

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonPath('redirect_url', route('student.quizzes.result', [
            'quiz' => $quiz->id,
            'attempt' => $attempt->id,
        ]));
});

test('get remaining time when elapsed returns timeout with redirect url', function () {
    ['user' => $user, 'quiz' => $quiz, 'attempt' => $attempt] = createStudentQuizAttempt('in_progress', 30);

    $response = $this->actingAs($user)
        ->getJson(route('student.quizzes.time', $attempt->id));

    $response->assertOk()
        ->assertJson([
            'timeout' => true,
        ])
        ->assertJsonPath('redirect_url', route('student.quizzes.result', [
            'quiz' => $quiz->id,
            'attempt' => $attempt->id,
        ]));

    expect($attempt->fresh()->status)->toBe('timed_out');
});

test('save answer with empty payload does not wipe existing answer', function () {
    ['user' => $user, 'quiz' => $quiz, 'attempt' => $attempt] = createStudentQuizAttempt('in_progress');

    $question = \App\Models\Question::create([
        'title' => 'Q1',
        'type' => 'true_false',
        'default_points' => 1,
    ]);

    $quiz->questions()->attach($question->id, ['order' => 1]);

    $option = \App\Models\QuestionOption::create([
        'question_id' => $question->id,
        'content' => 'صح',
        'is_correct' => true,
        'order' => 1,
    ]);

    \App\Models\QuizAnswer::create([
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'selected_options' => [$option->id],
        'answered_at' => now(),
        'max_points' => 1,
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('student.quizzes.save-answer', $attempt->id), [
            'question_id' => $question->id,
        ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $answer = \App\Models\QuizAnswer::where('attempt_id', $attempt->id)
        ->where('question_id', $question->id)
        ->first();

    expect($answer->selected_options)->toBe([$option->id]);
});

test('submit in progress quiz succeeds when gamification throws broadcast error', function () {
    ['user' => $user, 'quiz' => $quiz, 'attempt' => $attempt] = createStudentQuizAttempt('in_progress', 60);
    $attempt->update(['started_at' => now()]);

    $this->mock(GamificationService::class, function ($mock) {
        $mock->shouldReceive('processQuizCompletion')
            ->once()
            ->andThrow(new RuntimeException('Pusher error: cURL error 7'));
    });

    $response = $this->actingAs($user)
        ->postJson(route('student.quizzes.submit', $attempt->id));

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonPath('redirect_url', route('student.quizzes.result', [
            'quiz' => $quiz->id,
            'attempt' => $attempt->id,
        ]));

    expect($attempt->fresh()->status)->toBe('completed');
});
