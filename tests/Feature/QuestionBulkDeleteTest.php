<?php

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizQuestion;
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

function bulkDeleteAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

function createBulkDeleteSubject(): Subject
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

    return Subject::create([
        'name' => 'Subject '.$suffix,
        'slug' => 'subject-'.$suffix,
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);
}

test('subject question bank bulk delete removes selected questions', function () {
    $admin = bulkDeleteAdmin();
    $subject = createBulkDeleteSubject();

    $q1 = Question::create([
        'subject_id' => $subject->id,
        'type' => 'single_choice',
        'title' => 'Bulk delete Q1',
        'default_points' => 1,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $q2 = Question::create([
        'subject_id' => $subject->id,
        'type' => 'single_choice',
        'title' => 'Bulk delete Q2',
        'default_points' => 1,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $q3 = Question::create([
        'subject_id' => $subject->id,
        'type' => 'single_choice',
        'title' => 'Bulk delete Q3',
        'default_points' => 1,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->delete(
        route('admin.subjects.questions.destroy-multiple', $subject),
        ['question_ids' => [$q1->id, $q2->id]]
    );

    $response->assertRedirect(route('admin.subjects.questions.index', $subject));
    $response->assertSessionHas('success');

    expect(Question::withTrashed()->find($q1->id)?->trashed())->toBeTrue();
    expect(Question::withTrashed()->find($q2->id)?->trashed())->toBeTrue();
    expect(Question::find($q3->id))->not->toBeNull();
});

test('subject question bank bulk delete skips questions used in quizzes', function () {
    $admin = bulkDeleteAdmin();
    $subject = createBulkDeleteSubject();

    $deletable = Question::create([
        'subject_id' => $subject->id,
        'type' => 'single_choice',
        'title' => 'Deletable',
        'default_points' => 1,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $inQuiz = Question::create([
        'subject_id' => $subject->id,
        'type' => 'single_choice',
        'title' => 'In quiz',
        'default_points' => 1,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subject->id,
        'title' => 'Test Quiz',
        'duration_minutes' => 10,
        'show_timer' => true,
        'is_active' => true,
        'is_published' => false,
    ]);

    QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'question_id' => $inQuiz->id,
        'order' => 1,
        'points' => 1,
    ]);

    $response = $this->actingAs($admin)->delete(
        route('admin.subjects.questions.destroy-multiple', $subject),
        ['question_ids' => [$deletable->id, $inQuiz->id]]
    );

    $response->assertRedirect(route('admin.subjects.questions.index', $subject));
    $response->assertSessionHas('warning');

    expect(Question::withTrashed()->find($deletable->id)?->trashed())->toBeTrue();
    expect(Question::find($inQuiz->id))->not->toBeNull();
});

test('subject question bank bulk delete ignores questions from other subjects', function () {
    $admin = bulkDeleteAdmin();
    $subject = createBulkDeleteSubject();

    $otherSubject = Subject::create([
        'name' => 'Other '.uniqid(),
        'slug' => 'other-'.uniqid(),
        'class_id' => $subject->class_id,
        'order' => 2,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $mine = Question::create([
        'subject_id' => $subject->id,
        'type' => 'single_choice',
        'title' => 'Mine',
        'default_points' => 1,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $other = Question::create([
        'subject_id' => $otherSubject->id,
        'type' => 'single_choice',
        'title' => 'Other',
        'default_points' => 1,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->delete(
        route('admin.subjects.questions.destroy-multiple', $subject),
        ['question_ids' => [$mine->id, $other->id]]
    );

    $response->assertRedirect(route('admin.subjects.questions.index', $subject));
    $response->assertSessionHas('success');

    expect(Question::withTrashed()->find($mine->id)?->trashed())->toBeTrue();
    expect(Question::find($other->id))->not->toBeNull();
});
