<?php

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Question;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
    if (! Schema::hasTable('quizzes') || ! Schema::hasColumn('quizzes', 'copied_from_quiz_id')) {
        $this->markTestSkipped('Run migrations including copied_from_quiz_id on MySQL.');
    }
});

function quizRelinkAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    foreach (['quiz-duplicate', 'quiz-edit', 'quiz-create', 'quiz-approve-review'] as $permissionName) {
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        if (! $adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }
    }

    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole($adminRole);

    return $admin;
}

function createQuizRelinkFixture(): array
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

    $sectionA = SubjectSection::create([
        'subject_id' => $subjectA->id,
        'title' => 'Section A '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $sectionB = SubjectSection::create([
        'subject_id' => $subjectB->id,
        'title' => 'Section B '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $unitA = Unit::create([
        'section_id' => $sectionA->id,
        'title' => 'Unit A '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $unitB = Unit::create([
        'section_id' => $sectionB->id,
        'title' => 'Unit B '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $lessonA = Lesson::create([
        'unit_id' => $unitA->id,
        'section_id' => $sectionA->id,
        'title' => 'Lesson A '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $lessonB = Lesson::create([
        'unit_id' => $unitB->id,
        'section_id' => $sectionB->id,
        'title' => 'Lesson B '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $question = Question::create([
        'title' => 'Question '.$suffix,
        'type' => 'single_choice',
        'content' => 'Test?',
        'is_active' => true,
        'created_by' => User::factory()->create()->id,
    ]);

    $quiz = Quiz::create([
        'subject_id' => $subjectA->id,
        'section_id' => $sectionA->id,
        'unit_id' => $unitA->id,
        'lesson_id' => $lessonA->id,
        'scope' => 'lesson',
        'title' => 'Lesson Quiz '.$suffix,
        'pass_percentage' => 50,
        'grading_method' => 'highest',
        'review_options' => 'immediately',
        'is_active' => true,
        'is_published' => true,
        'review_status' => Quiz::REVIEW_STATUS_APPROVED,
    ]);

    QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'question_id' => $question->id,
        'order' => 1,
        'points' => 1,
    ]);

    return compact('quiz', 'subjectA', 'sectionA', 'unitA', 'subjectB', 'sectionB', 'unitB', 'lessonB', 'lessonA');
}

function quizUpdatePayload(array $overrides = []): array
{
    return array_merge([
        'quiz_relink' => '1',
        'title' => 'Updated Quiz Title',
        'pass_percentage' => 50,
        'grading_method' => 'highest',
        'review_options' => 'immediately',
    ], $overrides);
}

test('duplicate clears placement and sets copied_from_quiz_id', function () {
    ['quiz' => $quiz] = createQuizRelinkFixture();
    $admin = quizRelinkAdmin();

    $response = $this->actingAs($admin)->post(route('admin.quizzes.duplicate', $quiz));

    $response->assertRedirect();

    $copy = Quiz::where('copied_from_quiz_id', $quiz->id)->first();

    expect($copy)->not->toBeNull()
        ->and($copy->subject_id)->toBeNull()
        ->and($copy->unit_id)->toBeNull()
        ->and($copy->section_id)->toBeNull()
        ->and($copy->lesson_id)->toBeNull()
        ->and($copy->is_published)->toBeFalse()
        ->and($copy->needsRelink())->toBeTrue();
});

test('relinking copied quiz as unit quiz places it in target unit', function () {
    ['quiz' => $quiz, 'subjectB' => $subjectB, 'sectionB' => $sectionB, 'unitB' => $unitB] = createQuizRelinkFixture();
    $admin = quizRelinkAdmin();

    $this->actingAs($admin)->post(route('admin.quizzes.duplicate', $quiz));
    $copy = Quiz::where('copied_from_quiz_id', $quiz->id)->firstOrFail();

    $response = $this->actingAs($admin)->put(route('admin.quizzes.update', $copy), quizUpdatePayload([
        'scope' => 'unit',
        'subject_id' => $subjectB->id,
        'section_id' => $sectionB->id,
        'unit_id' => $unitB->id,
    ]));

    $response->assertRedirect(route('admin.quizzes.show', $copy->id));

    $copy->refresh();

    expect($copy->copied_from_quiz_id)->toBeNull()
        ->and($copy->subject_id)->toBe($subjectB->id)
        ->and($copy->unit_id)->toBe($unitB->id)
        ->and($copy->section_id)->toBe($sectionB->id)
        ->and($copy->lesson_id)->toBeNull()
        ->and($copy->scope)->toBe('unit');

    $unitB->refresh();
    expect($unitB->allUnitQuizzes()->pluck('id'))->toContain($copy->id);
});

test('relinking copied quiz as lesson quiz sets lesson in target unit', function () {
    ['quiz' => $quiz, 'subjectB' => $subjectB, 'sectionB' => $sectionB, 'unitB' => $unitB, 'lessonB' => $lessonB] = createQuizRelinkFixture();
    $admin = quizRelinkAdmin();

    $this->actingAs($admin)->post(route('admin.quizzes.duplicate', $quiz));
    $copy = Quiz::where('copied_from_quiz_id', $quiz->id)->firstOrFail();

    $response = $this->actingAs($admin)->put(route('admin.quizzes.update', $copy), quizUpdatePayload([
        'scope' => 'lesson',
        'subject_id' => $subjectB->id,
        'section_id' => $sectionB->id,
        'unit_id' => $unitB->id,
        'lesson_id' => $lessonB->id,
    ]));

    $response->assertRedirect(route('admin.quizzes.show', $copy->id));

    $copy->refresh();

    expect($copy->lesson_id)->toBe($lessonB->id)
        ->and($copy->scope)->toBe('lesson')
        ->and($copy->unit_id)->toBe($unitB->id);

    expect(Quiz::where('lesson_id', $lessonB->id)->where('id', $copy->id)->exists())->toBeTrue();
});

test('getUnits returns section label for disambiguation', function () {
    ['subjectA' => $subjectA, 'sectionA' => $sectionA, 'unitA' => $unitA] = createQuizRelinkFixture();
    $admin = quizRelinkAdmin();

    $response = $this->actingAs($admin)->getJson(route('admin.quizzes.get-units', [
        'subject_id' => $subjectA->id,
        'section_id' => $sectionA->id,
    ]));

    $response->assertOk();
    $payload = $response->json();

    expect($payload)->toBeArray()
        ->and($payload[0]['section_title'])->toBe($sectionA->title)
        ->and($payload[0]['label'])->toContain($sectionA->title)
        ->and($payload[0]['id'])->toBe($unitA->id);
});

test('approve review activates quiz for student visibility', function () {
    ['quiz' => $quiz, 'subjectB' => $subjectB, 'sectionB' => $sectionB, 'unitB' => $unitB] = createQuizRelinkFixture();
    $admin = quizRelinkAdmin();

    $this->actingAs($admin)->post(route('admin.quizzes.duplicate', $quiz));
    $copy = Quiz::where('copied_from_quiz_id', $quiz->id)->firstOrFail();

    $this->actingAs($admin)->put(route('admin.quizzes.update', $copy), quizUpdatePayload([
        'scope' => 'unit',
        'subject_id' => $subjectB->id,
        'section_id' => $sectionB->id,
        'unit_id' => $unitB->id,
        'is_active' => '1',
    ]));

    $copy->update([
        'review_status' => Quiz::REVIEW_STATUS_PENDING,
        'is_published' => false,
        'is_active' => false,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.quizzes.approve-review', $copy), []);

    $response->assertRedirect();
    $copy->refresh();

    expect($copy->review_status)->toBe(Quiz::REVIEW_STATUS_APPROVED)
        ->and($copy->is_published)->toBeTrue()
        ->and($copy->is_active)->toBeTrue();
});

test('updating unit on lesson quiz converts to unit quiz when lesson mismatches', function () {
    ['quiz' => $quiz, 'subjectB' => $subjectB, 'sectionB' => $sectionB, 'unitB' => $unitB, 'lessonA' => $lessonA] = createQuizRelinkFixture();
    $admin = quizRelinkAdmin();

    $quiz->update(['copied_from_quiz_id' => null]);

    $response = $this->actingAs($admin)->put(route('admin.quizzes.update', $quiz), quizUpdatePayload([
        'scope' => 'unit',
        'subject_id' => $subjectB->id,
        'unit_id' => $unitB->id,
        'section_id' => $sectionB->id,
    ]));

    $response->assertRedirect();
    $quiz->refresh();

    expect($quiz->lesson_id)->toBeNull()
        ->and($quiz->scope)->toBe('unit')
        ->and($quiz->unit_id)->toBe($unitB->id)
        ->and($quiz->subject_id)->toBe($subjectB->id);
});
