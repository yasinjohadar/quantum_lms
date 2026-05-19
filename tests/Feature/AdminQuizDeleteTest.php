<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Role;
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

function createQuizWithAttempt(string $attemptStatus = 'completed'): array
{
    $student = User::factory()->create();

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
        'title' => 'Quiz Delete Test',
        'duration_minutes' => 30,
        'show_timer' => true,
        'is_active' => true,
        'is_published' => true,
    ]);

    $attempt = QuizAttempt::create([
        'user_id' => $student->id,
        'quiz_id' => $quiz->id,
        'attempt_number' => 1,
        'started_at' => now(),
        'status' => $attemptStatus,
        'max_score' => 10,
    ]);

    return compact('student', 'quiz', 'attempt');
}

test('admin can delete quiz that has student attempts', function () {
    ['quiz' => $quiz, 'attempt' => $attempt] = createQuizWithAttempt('completed');

    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $response = $this->actingAs($admin)
        ->delete(route('admin.quizzes.destroy', $quiz->id));

    $response->assertRedirect(route('admin.quizzes.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('quizzes', ['id' => $quiz->id]);
    $this->assertSoftDeleted('quiz_attempts', ['id' => $attempt->id]);
});
