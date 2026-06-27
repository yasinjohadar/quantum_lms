<?php

use App\Models\Lesson;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\User;
use App\Services\Curriculum\LessonCloneService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
    if (! Schema::hasTable('lessons') || ! Schema::hasColumn('lessons', 'cloned_from_lesson_id')) {
        $this->markTestSkipped('Lesson sync migrations not applied; run migrations on MySQL.');
    }
});

function lessonsIndexAdmin(array $permissions = ['lesson-list', 'lesson-show']): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    foreach ($permissions as $permissionName) {
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        if (! $adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }
    }

    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole($adminRole);

    return $admin;
}

function createLessonsIndexCurriculum(string $suffix = ''): array
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

    $targetSection = SubjectSection::create([
        'subject_id' => $otherSubject->id,
        'title' => 'Target Section '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $unit = Unit::create([
        'section_id' => $section->id,
        'title' => 'Unit '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $targetUnit = Unit::create([
        'section_id' => $targetSection->id,
        'title' => 'Target Unit '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $lesson = Lesson::create([
        'unit_id' => $unit->id,
        'section_id' => $section->id,
        'title' => 'Lesson '.$suffix,
        'order' => 1,
        'is_active' => true,
        'review_status' => Lesson::REVIEW_STATUS_APPROVED,
    ]);

    return compact('lesson', 'unit', 'targetUnit', 'subject', 'schoolClass');
}

test('admin can view lessons index', function () {
    ['lesson' => $lesson] = createLessonsIndexCurriculum('index-view');
    $admin = lessonsIndexAdmin();

    $response = $this->actingAs($admin)->get(route('admin.lessons.index'));

    $response->assertOk();
    $response->assertSee('فهرس الدروس');
    $response->assertSee($lesson->title);
});

test('lessons index returns 403 without lesson-list permission', function () {
    createLessonsIndexCurriculum('index-403');
    $admin = lessonsIndexAdmin([]);

    $this->actingAs($admin)
        ->get(route('admin.lessons.index'))
        ->assertForbidden();
});

test('link_role mirror filter returns only sync mirrors', function () {
    ['lesson' => $lesson, 'targetUnit' => $targetUnit] = createLessonsIndexCurriculum('mirror-filter');

    app(LessonCloneService::class)->cloneLessonToUnit($lesson, $targetUnit);

    $mirror = Lesson::query()
        ->where('cloned_from_lesson_id', $lesson->id)
        ->where('unit_id', $targetUnit->id)
        ->first();

    expect($mirror)->not->toBeNull();

    $admin = lessonsIndexAdmin();

    $response = $this->actingAs($admin)->get(route('admin.lessons.index', [
        'link_role' => 'mirror',
    ]));

    $response->assertOk();
    $response->assertSee('>'.$mirror->id.'<', false);

    $originalOnly = $this->actingAs($admin)->get(route('admin.lessons.index', [
        'link_role' => 'original',
        'search' => $lesson->title,
    ]));

    $originalOnly->assertOk();
    $originalOnly->assertSee('>'.$lesson->id.'<', false);
    $originalOnly->assertDontSee('>'.$mirror->id.'<', false);
});

test('link_presence has_sync filter returns canonical lesson with mirrors', function () {
    ['lesson' => $lesson, 'targetUnit' => $targetUnit] = createLessonsIndexCurriculum('has-sync');

    app(LessonCloneService::class)->cloneLessonToUnit($lesson, $targetUnit);

    $admin = lessonsIndexAdmin();

    $response = $this->actingAs($admin)->get(route('admin.lessons.index', [
        'link_presence' => 'has_sync',
    ]));

    $response->assertOk();
    $response->assertSee($lesson->title);
});

test('search filter narrows lessons by title', function () {
    ['lesson' => $lesson] = createLessonsIndexCurriculum('search-hit');
    createLessonsIndexCurriculum('search-miss');

    $admin = lessonsIndexAdmin();

    $response = $this->actingAs($admin)->get(route('admin.lessons.index', [
        'search' => $lesson->title,
    ]));

    $response->assertOk();
    $response->assertSee($lesson->title);
});
