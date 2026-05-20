<?php

use App\Models\Question;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
});

function subjectBankAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

function createSubjectCurriculum(string $suffix = ''): array
{
    $suffix = $suffix ?: uniqid();

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

    $subject = Subject::create([
        'name' => 'Subject '.$suffix,
        'slug' => 'subject-'.$suffix,
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $otherSubject = Subject::create([
        'name' => 'Other '.$suffix,
        'slug' => 'other-'.$suffix,
        'class_id' => $schoolClass->id,
        'order' => 2,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $section = SubjectSection::create([
        'subject_id' => $subject->id,
        'title' => 'Section '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $unit = Unit::create([
        'section_id' => $section->id,
        'title' => 'Unit '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    return compact('subject', 'otherSubject', 'unit', 'schoolClass');
}

test('subject question bank lists only questions for that subject', function () {
    $admin = subjectBankAdmin();
    ['subject' => $subject, 'otherSubject' => $other, 'unit' => $unit] = createSubjectCurriculum('a');

    $inSubjectDirect = Question::create([
        'type' => 'single_choice',
        'title' => 'Direct subject question',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $subject->id,
    ]);

    $legacyViaUnit = Question::create([
        'type' => 'single_choice',
        'title' => 'Legacy unit question',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
    ]);
    $legacyViaUnit->units()->sync([$unit->id]);

    $otherQuestion = Question::create([
        'type' => 'single_choice',
        'title' => 'Other subject question',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $other->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.subjects.questions.index', $subject->id));

    $response->assertOk();
    $response->assertSee('Direct subject question');
    $response->assertSee('Legacy unit question');
    $response->assertDontSee('Other subject question');
});

test('main question bank still lists all questions', function () {
    $admin = subjectBankAdmin();
    ['subject' => $subject, 'otherSubject' => $other] = createSubjectCurriculum('b');

    Question::create([
        'type' => 'single_choice',
        'title' => 'Subject B question',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $subject->id,
    ]);

    Question::create([
        'type' => 'single_choice',
        'title' => 'Other B question',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $other->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.questions.index'));

    $response->assertOk();
    $response->assertSee('Subject B question');
    $response->assertSee('Other B question');
});

test('storing question from subject context sets subject_id', function () {
    $admin = subjectBankAdmin();
    ['subject' => $subject, 'unit' => $unit] = createSubjectCurriculum('c');

    $response = $this->actingAs($admin)
        ->post(route('admin.questions.store'), [
            'type' => 'single_choice',
            'title' => 'New subject scoped question',
            'difficulty' => 'medium',
            'default_points' => 2,
            'is_active' => '1',
            'subject_id' => $subject->id,
            'units' => [$unit->id],
            'options' => [
                ['content' => 'A', 'is_correct' => '1'],
                ['content' => 'B', 'is_correct' => '0'],
            ],
        ]);

    $response->assertRedirect(route('admin.subjects.questions.index', $subject->id));

    $question = Question::where('title', 'New subject scoped question')->first();
    expect($question)->not->toBeNull();
    expect($question->subject_id)->toBe($subject->id);
    expect($question->units->pluck('id')->all())->toContain($unit->id);
});

test('subject bank ai-create-from-image redirects with subject_id', function () {
    $admin = subjectBankAdmin();
    ['subject' => $subject] = createSubjectCurriculum('img-redirect');

    $response = $this->actingAs($admin)
        ->get(route('admin.subjects.questions.ai-create-from-image', $subject->id));

    $response->assertRedirect(route('admin.ai.question-generations.create-from-image', ['subject_id' => $subject->id]));
});

test('create from image with subject_id locks subject for form', function () {
    $admin = subjectBankAdmin();
    ['subject' => $subject] = createSubjectCurriculum('img-prefill');

    $response = $this->actingAs($admin)
        ->get(route('admin.ai.question-generations.create-from-image', ['subject_id' => $subject->id]));

    $response->assertOk();
    $response->assertViewHas('lockedSubject', fn ($locked) => $locked && (int) $locked->id === (int) $subject->id);
    $response->assertViewHas('prefillSubjectId', fn ($id) => (int) $id === (int) $subject->id);
    $response->assertSee('ستُربط بمادة');
    $response->assertSee($subject->name);
});
