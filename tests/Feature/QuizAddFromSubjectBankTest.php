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

function quizAddBankAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

function createQuizAddCurriculum(): array
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

    return compact('subjectA', 'subjectB');
}

test('quizzes for add returns only quizzes of the subject', function () {
    $admin = quizAddBankAdmin();
    ['subjectA' => $subjectA, 'subjectB' => $subjectB] = createQuizAddCurriculum();

    $quizA = Quiz::create([
        'subject_id' => $subjectA->id,
        'title' => 'Quiz A',
        'duration_minutes' => 10,
        'show_timer' => true,
        'is_active' => true,
        'is_published' => true,
    ]);

    Quiz::create([
        'subject_id' => $subjectB->id,
        'title' => 'Quiz B',
        'duration_minutes' => 10,
        'show_timer' => true,
        'is_active' => true,
        'is_published' => true,
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('admin.subjects.quizzes.for-add', $subjectA->id));

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $ids = collect($response->json('quizzes'))->pluck('id')->all();
    expect($ids)->toContain($quizA->id);
    expect($ids)->not->toContain(Quiz::where('subject_id', $subjectB->id)->value('id'));
});

test('add question to quiz succeeds for same subject', function () {
    $admin = quizAddBankAdmin();
    ['subjectA' => $subjectA] = createQuizAddCurriculum();

    $quiz = Quiz::create([
        'subject_id' => $subjectA->id,
        'title' => 'Target Quiz',
        'duration_minutes' => 10,
        'show_timer' => true,
        'is_active' => true,
        'is_published' => true,
    ]);

    $question = Question::create([
        'type' => 'single_choice',
        'title' => 'Same subject question',
        'difficulty' => 'medium',
        'default_points' => 5,
        'is_active' => true,
        'subject_id' => $subjectA->id,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('admin.quizzes.add-question', $quiz->id), [
            'question_id' => $question->id,
        ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    expect($quiz->questions()->where('question_id', $question->id)->exists())->toBeTrue();
});

test('add question to quiz rejects question from other subject', function () {
    $admin = quizAddBankAdmin();
    ['subjectA' => $subjectA, 'subjectB' => $subjectB] = createQuizAddCurriculum();

    $quiz = Quiz::create([
        'subject_id' => $subjectA->id,
        'title' => 'Quiz A only',
        'duration_minutes' => 10,
        'show_timer' => true,
        'is_active' => true,
        'is_published' => true,
    ]);

    $otherQuestion = Question::create([
        'type' => 'single_choice',
        'title' => 'Other subject question',
        'difficulty' => 'medium',
        'default_points' => 5,
        'is_active' => true,
        'subject_id' => $subjectB->id,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('admin.quizzes.add-question', $quiz->id), [
            'question_id' => $otherQuestion->id,
        ]);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    expect($quiz->questions()->where('question_id', $otherQuestion->id)->exists())->toBeFalse();
});
