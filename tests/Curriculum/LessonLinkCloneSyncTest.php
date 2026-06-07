<?php

use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\SectionSyncPeer;
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
    if (! Schema::hasTable('lessons') || ! Schema::hasColumn('lessons', 'sync_group_id')) {
        $this->markTestSkipped('Lesson sync migrations not applied; run migrations on MySQL.');
    }
});

function lessonLinkAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    foreach (['lesson-edit', 'lesson-delete'] as $permissionName) {
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        if (! $adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }
    }

    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole($adminRole);

    return $admin;
}

function createLessonLinkTestCurriculum(string $suffix = ''): array
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

    $history = Subject::create([
        'name' => 'History '.$suffix,
        'slug' => 'history-'.$suffix,
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $philosophy = Subject::create([
        'name' => 'Philosophy '.$suffix,
        'slug' => 'philosophy-'.$suffix,
        'class_id' => $schoolClass->id,
        'order' => 2,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $homeSection = SubjectSection::create([
        'subject_id' => $history->id,
        'title' => 'Home Section '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $targetSection = SubjectSection::create([
        'subject_id' => $philosophy->id,
        'title' => 'Target Section '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $sourceUnit = Unit::create([
        'section_id' => $homeSection->id,
        'title' => 'Source Unit '.$suffix,
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
        'unit_id' => $sourceUnit->id,
        'section_id' => $homeSection->id,
        'title' => 'Lesson '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    LessonAttachment::create([
        'lesson_id' => $lesson->id,
        'type' => 'file',
        'title' => 'Attachment '.$suffix,
        'file_path' => 'lessons/test.pdf',
        'order' => 1,
        'is_active' => true,
    ]);

    Quiz::create([
        'subject_id' => $history->id,
        'section_id' => $homeSection->id,
        'unit_id' => $sourceUnit->id,
        'lesson_id' => $lesson->id,
        'title' => 'Lesson Quiz '.$suffix,
        'order' => 1,
        'is_active' => true,
        'is_published' => true,
        'scope' => 'lesson',
    ]);

    return compact('history', 'philosophy', 'homeSection', 'targetSection', 'sourceUnit', 'targetUnit', 'lesson');
}

test('linking lesson to another unit creates full mirror copy', function () {
    ['targetUnit' => $targetUnit, 'lesson' => $lesson] = createLessonLinkTestCurriculum('link');

    $admin = lessonLinkAdmin();

    $response = $this->actingAs($admin)->post(route('admin.lessons.link-units', $lesson), [
        'linked_targets' => [
            ['unit_id' => $targetUnit->id],
        ],
    ]);

    $response->assertRedirect();

    $mirror = Lesson::query()
        ->where('cloned_from_lesson_id', $lesson->id)
        ->where('unit_id', $targetUnit->id)
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->sync_group_id)->not->toBeNull()
        ->and($mirror->is_sync_canonical)->toBeFalse()
        ->and($mirror->title)->toBe($lesson->title);

    expect(LessonAttachment::where('lesson_id', $mirror->id)->count())->toBe(1);
    expect(Quiz::where('lesson_id', $mirror->id)->where('unit_id', $targetUnit->id)->count())->toBe(1);

    expect(SectionSyncPeer::query()
        ->where('entity_type', SectionSyncPeer::TYPE_LESSON)
        ->where('canonical_entity_id', $lesson->id)
        ->where('peer_entity_id', $mirror->id)
        ->exists())->toBeTrue();
});

test('updating canonical lesson title syncs to mirror', function () {
    ['targetUnit' => $targetUnit, 'lesson' => $lesson] = createLessonLinkTestCurriculum('sync-c');

    app(LessonCloneService::class)->cloneLessonToUnit($lesson, $targetUnit);

    $lesson->update(['title' => 'Updated Canonical Title']);

    $mirror = Lesson::query()
        ->where('cloned_from_lesson_id', $lesson->id)
        ->where('unit_id', $targetUnit->id)
        ->first();

    expect($mirror->fresh()->title)->toBe('Updated Canonical Title');
});

test('linking section direct lesson to target unit', function () {
    ['targetUnit' => $targetUnit, 'homeSection' => $homeSection, 'history' => $history] = createLessonLinkTestCurriculum('section-direct');

    $sectionLesson = Lesson::create([
        'unit_id' => null,
        'section_id' => $homeSection->id,
        'title' => 'Section Lesson',
        'order' => 1,
        'is_active' => true,
    ]);

    app(LessonCloneService::class)->cloneLessonToUnit($sectionLesson, $targetUnit);

    $mirror = Lesson::query()
        ->where('cloned_from_lesson_id', $sectionLesson->id)
        ->where('unit_id', $targetUnit->id)
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->section_id)->toBe($targetUnit->section_id)
        ->and($mirror->unit_id)->toBe($targetUnit->id);
});

test('deleting canonical lesson keeps mirror in linked unit', function () {
    ['targetUnit' => $targetUnit, 'lesson' => $lesson] = createLessonLinkTestCurriculum('del-c');

    app(LessonCloneService::class)->cloneLessonToUnit($lesson, $targetUnit);

    $admin = lessonLinkAdmin();

    $this->actingAs($admin)->delete(route('admin.lessons.destroy', $lesson));

    expect(Lesson::withTrashed()->find($lesson->id)?->trashed())->toBeTrue();

    $mirror = Lesson::query()
        ->where('cloned_from_lesson_id', $lesson->id)
        ->where('unit_id', $targetUnit->id)
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->trashed())->toBeFalse();
});

test('removing link soft deletes mirror only', function () {
    ['targetUnit' => $targetUnit, 'lesson' => $lesson] = createLessonLinkTestCurriculum('unlink');

    app(LessonCloneService::class)->cloneLessonToUnit($lesson, $targetUnit);

    $admin = lessonLinkAdmin();

    $this->actingAs($admin)->post(route('admin.lessons.link-units', $lesson), [
        'linked_targets' => [],
    ]);

    $mirror = Lesson::withTrashed()
        ->where('cloned_from_lesson_id', $lesson->id)
        ->where('unit_id', $targetUnit->id)
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->trashed())->toBeTrue();

    expect(Lesson::find($lesson->id))->not->toBeNull()
        ->and(Lesson::find($lesson->id)->trashed())->toBeFalse();
});

test('get linked units returns mirror targets', function () {
    ['targetUnit' => $targetUnit, 'lesson' => $lesson] = createLessonLinkTestCurriculum('api');

    app(LessonCloneService::class)->cloneLessonToUnit($lesson, $targetUnit);

    $admin = lessonLinkAdmin();

    $response = $this->actingAs($admin)->getJson(route('admin.lessons.linked-units', $lesson));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $targetUnit->id]);
});
