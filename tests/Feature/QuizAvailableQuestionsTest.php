<?php

use App\Models\Question;
use App\Models\Quiz;
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

function quizQuestionsAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

function createQuizWithTwoSubjects(): array
{
    $suffix = uniqid();

    $stage = Stage::create([
        'name' => 'Stage '.$suffix,
        'slug' => 'stage-'.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $schoolClass = SchoolClass::create([
        'name' => 'Class '.$suffix,
        'slug' => 'class-'.$suffix,
        'stage_id' => $stage->id,
        'order' => 1,
        'is_active' => true,
    ]);

    $subjectA = Subject::create([
        'name' => 'Subject A '.$suffix,
        'slug' => 'subject-a-'.$suffix,
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $subjectB = Subject::create([
        'name' => 'Subject B '.$suffix,
        'slug' => 'subject-b-'.$suffix,
        'class_id' => $schoolClass->id,
        'order' => 2,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subjectA->id,
        'title' => 'Quiz '.$suffix,
        'duration_minutes' => 30,
        'show_timer' => true,
        'is_active' => true,
        'is_published' => true,
    ]);

    return compact('quiz', 'subjectA', 'subjectB');
}

test('quiz questions page shows only questions for quiz subject', function () {
    $admin = quizQuestionsAdmin();
    ['quiz' => $quiz, 'subjectA' => $subjectA, 'subjectB' => $subjectB] = createQuizWithTwoSubjects();

    Question::create([
        'type' => 'single_choice',
        'title' => 'Question for quiz subject',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $subjectA->id,
    ]);

    Question::create([
        'type' => 'single_choice',
        'title' => 'Question for other subject',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $subjectB->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.quizzes.questions', $quiz->id));

    $response->assertOk();
    $response->assertSee('Question for quiz subject');
    $response->assertDontSee('Question for other subject');
    $response->assertSee($subjectA->name);
});

test('quiz questions ignores tampered subject_id query', function () {
    $admin = quizQuestionsAdmin();
    ['quiz' => $quiz, 'subjectA' => $subjectA, 'subjectB' => $subjectB] = createQuizWithTwoSubjects();

    Question::create([
        'type' => 'single_choice',
        'title' => 'Only quiz subject question',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $subjectA->id,
    ]);

    Question::create([
        'type' => 'single_choice',
        'title' => 'Other subject only',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $subjectB->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.quizzes.questions', [
            'quiz' => $quiz->id,
            'subject_id' => $subjectB->id,
        ]));

    $response->assertOk();
    $response->assertSee('Only quiz subject question');
    $response->assertDontSee('Other subject only');
});
