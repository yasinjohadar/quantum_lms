<?php

use App\Models\Question;
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

function questionBankFilterAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

function createFilterCurriculum(): array
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

    $otherSubject = Subject::create([
        'name' => 'Other '.$suffix,
        'slug' => 'other-'.$suffix,
        'class_id' => $schoolClass->id,
        'order' => 2,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    return compact('subject', 'otherSubject', 'schoolClass');
}

test('main question bank filters by subject_id', function () {
    $admin = questionBankFilterAdmin();
    ['subject' => $subject, 'otherSubject' => $other] = createFilterCurriculum();

    Question::create([
        'type' => 'single_choice',
        'title' => 'In filtered subject',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $subject->id,
    ]);

    Question::create([
        'type' => 'single_choice',
        'title' => 'In other subject',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $other->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.questions.index', ['subject_id' => $subject->id]));

    $response->assertOk();
    $response->assertSee('In filtered subject');
    $response->assertDontSee('In other subject');
});

test('main question bank ajax index returns results html', function () {
    $admin = questionBankFilterAdmin();
    ['subject' => $subject] = createFilterCurriculum();

    Question::create([
        'type' => 'single_choice',
        'title' => 'Ajax subject question',
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $subject->id,
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('admin.questions.index', ['subject_id' => $subject->id]), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonStructure(['html']);
    expect($response->json('html'))->toContain('Ajax subject question');
});
