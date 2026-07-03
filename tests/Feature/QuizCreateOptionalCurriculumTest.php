<?php

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

function quizCreateAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

function quizCreateCurriculum(): array
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

    $subject = Subject::create([
        'name' => 'Subject '.$suffix,
        'slug' => 'subject-'.$suffix,
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    return compact('stage', 'schoolClass', 'subject');
}

function minimalQuizPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Optional curriculum quiz',
        'pass_percentage' => 50,
        'grading_method' => 'highest',
        'review_options' => 'immediately',
        'is_active' => 1,
    ], $overrides);
}

test('admin can create quiz with title only and no curriculum', function () {
    $admin = quizCreateAdmin();

    $response = $this->actingAs($admin)
        ->post(route('admin.quizzes.store'), minimalQuizPayload());

    $quiz = Quiz::where('title', 'Optional curriculum quiz')->first();
    expect($quiz)->not->toBeNull();

    $response->assertRedirect(route('admin.quizzes.import-excel.show', $quiz));

    expect($quiz->subject_id)->toBeNull();
    expect($quiz->unit_id)->toBeNull();
});

test('admin can create quiz with subject only and no unit', function () {
    $admin = quizCreateAdmin();
    ['subject' => $subject] = quizCreateCurriculum();

    $response = $this->actingAs($admin)
        ->post(route('admin.quizzes.store'), minimalQuizPayload([
            'title' => 'Subject only quiz',
            'subject_id' => $subject->id,
        ]));

    $quiz = Quiz::where('title', 'Subject only quiz')->first();
    expect($quiz)->not->toBeNull();

    $response->assertRedirect(route('admin.quizzes.import-excel.show', $quiz));

    expect($quiz->subject_id)->toBe($subject->id);
    expect($quiz->unit_id)->toBeNull();
});

test('get classes by stage returns json without required params', function () {
    $admin = quizCreateAdmin();
    ['stage' => $stage, 'schoolClass' => $schoolClass] = quizCreateCurriculum();

    $response = $this->actingAs($admin)
        ->getJson(route('admin.quizzes.get-classes-by-stage'));

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $ids = collect($response->json('data'))->pluck('id')->all();
    expect($ids)->toContain($schoolClass->id);

    $filtered = $this->actingAs($admin)
        ->getJson(route('admin.quizzes.get-classes-by-stage', ['stage_id' => $stage->id]));

    $filtered->assertOk();
    $filteredIds = collect($filtered->json('data'))->pluck('id')->all();
    expect($filteredIds)->toContain($schoolClass->id);
});

test('get subjects by class returns json without required class id', function () {
    $admin = quizCreateAdmin();
    ['subject' => $subject, 'schoolClass' => $schoolClass, 'stage' => $stage] = quizCreateCurriculum();

    $all = $this->actingAs($admin)
        ->getJson(route('admin.quizzes.get-subjects-by-class'));

    $all->assertOk();
    $all->assertJsonPath('success', true);
    $allIds = collect($all->json('data'))->pluck('id')->all();
    expect($allIds)->toContain($subject->id);

    $byClass = $this->actingAs($admin)
        ->getJson(route('admin.quizzes.get-subjects-by-class', ['class_id' => $schoolClass->id]));

    $byClass->assertOk();
    expect(collect($byClass->json('data'))->pluck('id')->all())->toContain($subject->id);

    $byStage = $this->actingAs($admin)
        ->getJson(route('admin.quizzes.get-subjects-by-class', ['stage_id' => $stage->id]));

    $byStage->assertOk();
    expect(collect($byStage->json('data'))->pluck('id')->all())->toContain($subject->id);
});
