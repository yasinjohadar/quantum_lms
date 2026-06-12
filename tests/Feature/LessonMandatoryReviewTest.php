<?php

use App\Models\Lesson;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\SystemSetting;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }

    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
});

function seedLessonReviewPermissions(): void
{
    foreach ([
        'lesson-create',
        'lesson-edit',
        'lesson-approve-review',
        'settings-manage',
    ] as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }
}

function createLessonReviewCurriculum(): array
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

    return compact('schoolClass', 'subject', 'section', 'unit');
}

function createTeacherUser(): User
{
    $teacherRole = Role::updateOrCreate(
        ['name' => 'teacher', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
    );
    $teacherRole->syncPermissions(['lesson-create', 'lesson-edit']);

    $teacher = User::factory()->create(['is_active' => true]);
    $teacher->assignRole($teacherRole);

    return $teacher;
}

function createReviewerUser(): User
{
    $reviewerRole = Role::firstOrCreate(
        ['name' => 'lesson-reviewer', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $reviewerRole->syncPermissions(['lesson-approve-review', 'review-queue-list']);

    $reviewer = User::factory()->create(['is_active' => true]);
    $reviewer->assignRole($reviewerRole);

    return $reviewer;
}

function lessonPayload(): array
{
    return [
        'title' => 'درس اختبار',
        'video_type' => 'youtube',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'description' => 'وصف الدرس',
    ];
}

test('mandatory review setting forces teacher lesson create into pending review', function () {
    seedLessonReviewPermissions();
    SystemSetting::set('content_lesson_mandatory_review', '1', 'boolean', 'general');

    ['subject' => $subject, 'unit' => $unit] = createLessonReviewCurriculum();
    $teacher = createTeacherUser();
    $teacher->assignedSubjects()->attach($subject->id, [
        'assigned_by' => $teacher->id,
        'assigned_at' => now(),
    ]);

    $response = $this->actingAs($teacher)->post(route('admin.units.lessons.store', $unit), lessonPayload());

    $response->assertRedirect(route('admin.subjects.show', $subject->id));

    $lesson = Lesson::where('title', 'درس اختبار')->first();
    expect($lesson)->not->toBeNull();
    expect($lesson->review_status)->toBe(Lesson::REVIEW_STATUS_PENDING);
    expect($lesson->is_active)->toBeFalse();
    expect($lesson->submitted_for_review_at)->not->toBeNull();
});

test('mandatory review ignores is_active tampering on create', function () {
    seedLessonReviewPermissions();
    SystemSetting::set('content_lesson_mandatory_review', '1', 'boolean', 'general');

    ['unit' => $unit] = createLessonReviewCurriculum();
    $teacher = createTeacherUser();
    $teacher->assignedSubjects()->attach($unit->section->subject_id, [
        'assigned_by' => $teacher->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($teacher)->post(route('admin.units.lessons.store', $unit), array_merge(lessonPayload(), [
        'is_active' => '1',
    ]));

    $lesson = Lesson::where('title', 'درس اختبار')->first();
    expect($lesson->review_status)->toBe(Lesson::REVIEW_STATUS_PENDING);
    expect($lesson->is_active)->toBeFalse();
});

test('optional review keeps draft when teacher does not submit', function () {
    seedLessonReviewPermissions();
    SystemSetting::set('content_lesson_mandatory_review', '0', 'boolean', 'general');

    ['unit' => $unit] = createLessonReviewCurriculum();
    $teacher = createTeacherUser();
    $teacher->assignedSubjects()->attach($unit->section->subject_id, [
        'assigned_by' => $teacher->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($teacher)->post(route('admin.units.lessons.store', $unit), lessonPayload());

    $lesson = Lesson::where('title', 'درس اختبار')->first();
    expect($lesson->review_status)->toBe(Lesson::REVIEW_STATUS_DRAFT);
    expect($lesson->is_active)->toBeFalse();
});

test('optional review submits when teacher enables switch', function () {
    seedLessonReviewPermissions();
    SystemSetting::set('content_lesson_mandatory_review', '0', 'boolean', 'general');

    ['unit' => $unit] = createLessonReviewCurriculum();
    $teacher = createTeacherUser();
    $teacher->assignedSubjects()->attach($unit->section->subject_id, [
        'assigned_by' => $teacher->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($teacher)->post(route('admin.units.lessons.store', $unit), array_merge(lessonPayload(), [
        'is_active' => '1',
    ]));

    $lesson = Lesson::where('title', 'درس اختبار')->first();
    expect($lesson->review_status)->toBe(Lesson::REVIEW_STATUS_PENDING);
});

test('reviewer can approve pending lesson', function () {
    seedLessonReviewPermissions();
    ['section' => $section, 'unit' => $unit] = createLessonReviewCurriculum();
    $reviewer = createReviewerUser();

    $lesson = Lesson::create(array_merge(lessonPayload(), [
        'unit_id' => $unit->id,
        'section_id' => $section->id,
        'review_status' => Lesson::REVIEW_STATUS_PENDING,
        'is_active' => false,
        'submitted_for_review_at' => now(),
        'order' => 1,
    ]));

    $this->actingAs($reviewer)->post(route('admin.lessons.approve-review', $lesson));

    $lesson->refresh();
    expect($lesson->review_status)->toBe(Lesson::REVIEW_STATUS_APPROVED);
    expect($lesson->is_active)->toBeTrue();
});

test('mandatory review re-submits approved lesson when teacher updates', function () {
    seedLessonReviewPermissions();
    SystemSetting::set('content_lesson_mandatory_review', '1', 'boolean', 'general');

    ['subject' => $subject, 'unit' => $unit] = createLessonReviewCurriculum();
    $teacher = createTeacherUser();
    $teacher->assignedSubjects()->attach($subject->id, [
        'assigned_by' => $teacher->id,
        'assigned_at' => now(),
    ]);

    $lesson = Lesson::create(array_merge(lessonPayload(), [
        'unit_id' => $unit->id,
        'section_id' => $section->id,
        'review_status' => Lesson::REVIEW_STATUS_APPROVED,
        'is_active' => true,
        'order' => 1,
    ]));

    $this->actingAs($teacher)->put(route('admin.lessons.update', $lesson), array_merge(lessonPayload(), [
        'title' => 'درس معدّل',
    ]));

    $lesson->refresh();
    expect($lesson->review_status)->toBe(Lesson::REVIEW_STATUS_PENDING);
    expect($lesson->is_active)->toBeFalse();
});

test('admin can toggle mandatory review setting', function () {
    seedLessonReviewPermissions();
    SystemSetting::set('content_lesson_mandatory_review', '0', 'boolean', 'general');

    $adminRole = Role::firstOrCreate(
        ['name' => 'settings-admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $adminRole->syncPermissions(['settings-manage']);

    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole($adminRole);

    $this->actingAs($admin)->put(route('admin.settings.update'), [
        'group' => 'general',
        'settings' => [
            'content_lesson_mandatory_review' => '1',
        ],
    ])->assertRedirect(route('admin.settings.index', ['group' => 'general']));

    expect(SystemSetting::lessonMandatoryReviewEnabled())->toBeTrue();
});
