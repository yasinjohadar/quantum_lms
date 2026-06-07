<?php

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\User;
use App\Services\Curriculum\SectionCloneService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
    if (! Schema::hasTable('subject_sections') || ! Schema::hasTable('section_sync_peers')) {
        $this->markTestSkipped('Database schema not migrated; run migrations on MySQL.');
    }
});

function sectionLinkAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    foreach (['subject-section-edit', 'subject-section-delete'] as $permissionName) {
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        if (! $adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }
    }

    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole($adminRole);

    return $admin;
}

function createLinkTestCurriculum(string $suffix = ''): array
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

    $section = SubjectSection::create([
        'subject_id' => $history->id,
        'title' => 'Ancient History '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $unit = Unit::create([
        'section_id' => $section->id,
        'title' => 'Unit '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $lesson = Lesson::create([
        'unit_id' => $unit->id,
        'section_id' => $section->id,
        'title' => 'Lesson '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $quiz = Quiz::create([
        'subject_id' => $history->id,
        'section_id' => $section->id,
        'unit_id' => $unit->id,
        'title' => 'Quiz '.$suffix,
        'order' => 1,
        'is_active' => true,
        'is_published' => true,
        'scope' => 'unit',
    ]);

    return compact('history', 'philosophy', 'section', 'unit', 'lesson', 'quiz');
}

test('linking section to another subject creates full mirror copy', function () {
    ['philosophy' => $philosophy, 'section' => $section] = createLinkTestCurriculum('link');

    $admin = sectionLinkAdmin();

    $response = $this->actingAs($admin)->post(
        route('admin.sections.link-subjects', $section),
        ['linked_subject_ids' => [$philosophy->id]]
    );

    $response->assertRedirect();

    $mirror = SubjectSection::query()
        ->where('cloned_from_section_id', $section->id)
        ->where('subject_id', $philosophy->id)
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->sync_group_id)->not->toBeNull()
        ->and($mirror->is_sync_canonical)->toBeFalse();

    expect(Unit::where('section_id', $mirror->id)->count())->toBe(1);
    expect(Lesson::where('section_id', $mirror->id)->count())->toBe(1);
    expect(Quiz::where('subject_id', $philosophy->id)->where('section_id', $mirror->id)->count())->toBe(1);
});

test('updating canonical section title syncs to mirror', function () {
    ['philosophy' => $philosophy, 'section' => $section] = createLinkTestCurriculum('sync-c');

    app(SectionCloneService::class)->cloneSectionTreeToSubject($section, $philosophy);

    $section->update(['title' => 'Updated Canonical Title']);

    $mirror = SubjectSection::query()
        ->where('cloned_from_section_id', $section->id)
        ->where('subject_id', $philosophy->id)
        ->first();

    expect($mirror->fresh()->title)->toBe('Updated Canonical Title');
});

test('updating mirror section title syncs to canonical', function () {
    ['philosophy' => $philosophy, 'section' => $section] = createLinkTestCurriculum('sync-m');

    app(SectionCloneService::class)->cloneSectionTreeToSubject($section, $philosophy);

    $mirror = SubjectSection::query()
        ->where('cloned_from_section_id', $section->id)
        ->where('subject_id', $philosophy->id)
        ->first();

    $mirror->update(['title' => 'Updated Mirror Title']);

    expect($section->fresh()->title)->toBe('Updated Mirror Title');
});

test('deleting canonical section keeps mirror in linked subject', function () {
    ['history' => $history, 'philosophy' => $philosophy, 'section' => $section] = createLinkTestCurriculum('del-c');

    app(SectionCloneService::class)->cloneSectionTreeToSubject($section, $philosophy);

    $admin = sectionLinkAdmin();

    $this->actingAs($admin)->delete(route('admin.subject-sections.destroy', $section));

    expect(SubjectSection::withTrashed()->find($section->id)?->trashed())->toBeTrue();

    $mirror = SubjectSection::query()
        ->where('cloned_from_section_id', $section->id)
        ->where('subject_id', $philosophy->id)
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->trashed())->toBeFalse()
        ->and(Unit::where('section_id', $mirror->id)->count())->toBe(1);

    expect(Subject::find($history->id))->not->toBeNull();
});

test('removing link soft deletes mirror only', function () {
    ['philosophy' => $philosophy, 'section' => $section] = createLinkTestCurriculum('unlink');

    app(SectionCloneService::class)->cloneSectionTreeToSubject($section, $philosophy);

    $admin = sectionLinkAdmin();

    $this->actingAs($admin)->post(
        route('admin.sections.link-subjects', $section),
        ['linked_subject_ids' => []]
    );

    $mirror = SubjectSection::withTrashed()
        ->where('cloned_from_section_id', $section->id)
        ->where('subject_id', $philosophy->id)
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->trashed())->toBeTrue();

    expect(SubjectSection::find($section->id))->not->toBeNull()
        ->and(SubjectSection::find($section->id)->trashed())->toBeFalse();
});

test('linking section as root in target subject sets parent_id null', function () {
    ['philosophy' => $philosophy, 'section' => $section] = createLinkTestCurriculum('root-link');

    $admin = sectionLinkAdmin();

    $this->actingAs($admin)->post(
        route('admin.sections.link-subjects', $section),
        [
            'linked_targets' => [
                ['subject_id' => $philosophy->id, 'parent_section_id' => ''],
            ],
        ]
    );

    $mirror = SubjectSection::query()
        ->where('cloned_from_section_id', $section->id)
        ->where('subject_id', $philosophy->id)
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->parent_id)->toBeNull();
});

test('linking section under parent in target subject', function () {
    ['philosophy' => $philosophy, 'section' => $section] = createLinkTestCurriculum('child-link');

    $parentInTarget = SubjectSection::create([
        'subject_id' => $philosophy->id,
        'title' => 'Parent in target',
        'order' => 1,
        'is_active' => true,
    ]);

    $admin = sectionLinkAdmin();

    $this->actingAs($admin)->post(
        route('admin.sections.link-subjects', $section),
        [
            'linked_targets' => [
                ['subject_id' => $philosophy->id, 'parent_section_id' => $parentInTarget->id],
            ],
        ]
    );

    $mirror = SubjectSection::query()
        ->where('cloned_from_section_id', $section->id)
        ->where('subject_id', $philosophy->id)
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->parent_id)->toBe($parentInTarget->id);
});

test('changing target parent recreates mirror in new placement', function () {
    ['philosophy' => $philosophy, 'section' => $section] = createLinkTestCurriculum('move-link');

    $parentInTarget = SubjectSection::create([
        'subject_id' => $philosophy->id,
        'title' => 'Parent in target',
        'order' => 1,
        'is_active' => true,
    ]);

    $admin = sectionLinkAdmin();

    $this->actingAs($admin)->post(
        route('admin.sections.link-subjects', $section),
        [
            'linked_targets' => [
                ['subject_id' => $philosophy->id, 'parent_section_id' => ''],
            ],
        ]
    );

    $rootMirror = SubjectSection::query()
        ->where('cloned_from_section_id', $section->id)
        ->where('subject_id', $philosophy->id)
        ->first();

    expect($rootMirror)->not->toBeNull()
        ->and($rootMirror->parent_id)->toBeNull();

    $this->actingAs($admin)->post(
        route('admin.sections.link-subjects', $section),
        [
            'linked_targets' => [
                ['subject_id' => $philosophy->id, 'parent_section_id' => $parentInTarget->id],
            ],
        ]
    );

    expect(SubjectSection::withTrashed()->find($rootMirror->id)?->trashed())->toBeTrue();

    $childMirror = SubjectSection::query()
        ->where('cloned_from_section_id', $section->id)
        ->where('subject_id', $philosophy->id)
        ->whereNull('deleted_at')
        ->first();

    expect($childMirror)->not->toBeNull()
        ->and($childMirror->id)->not->toBe($rootMirror->id)
        ->and($childMirror->parent_id)->toBe($parentInTarget->id);

    $section->update(['title' => 'Moved Mirror Sync Title']);

    expect($childMirror->fresh()->title)->toBe('Moved Mirror Sync Title');
});
