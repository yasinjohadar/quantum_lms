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
use App\Services\Curriculum\UnitCloneService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
    if (! Schema::hasTable('units') || ! Schema::hasColumn('units', 'sync_group_id')) {
        $this->markTestSkipped('Unit sync migrations not applied; run migrations on MySQL.');
    }
});

function unitLinkAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    foreach (['unit-edit', 'unit-delete'] as $permissionName) {
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        if (! $adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }
    }

    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole($adminRole);

    return $admin;
}

function createUnitLinkTestCurriculum(string $suffix = ''): array
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

    $unit = Unit::create([
        'section_id' => $homeSection->id,
        'title' => 'Unit '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $lesson = Lesson::create([
        'unit_id' => $unit->id,
        'section_id' => $homeSection->id,
        'title' => 'Lesson '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    $quiz = Quiz::create([
        'subject_id' => $history->id,
        'section_id' => $homeSection->id,
        'unit_id' => $unit->id,
        'title' => 'Quiz '.$suffix,
        'order' => 1,
        'is_active' => true,
        'is_published' => true,
        'scope' => 'unit',
    ]);

    return compact('history', 'philosophy', 'homeSection', 'targetSection', 'unit', 'lesson', 'quiz');
}

test('linking unit to another section creates full mirror copy', function () {
    ['targetSection' => $targetSection, 'unit' => $unit, 'homeSection' => $homeSection] = createUnitLinkTestCurriculum('link');

    $admin = unitLinkAdmin();

    $response = $this->actingAs($admin)->put(route('admin.units.update', $unit), [
        'title' => $unit->title,
        'order' => $unit->order,
        'is_active' => true,
        'sync_mirrored_sections' => true,
        'linked_section_ids' => [$targetSection->id],
    ]);

    $response->assertRedirect();

    $mirror = Unit::query()
        ->where('cloned_from_unit_id', $unit->id)
        ->where('section_id', $targetSection->id)
        ->whereNull('parent_id')
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->sync_group_id)->not->toBeNull()
        ->and($mirror->is_sync_canonical)->toBeFalse();

    expect(Lesson::where('unit_id', $mirror->id)->count())->toBe(1);
    expect(Quiz::where('unit_id', $mirror->id)->where('section_id', $targetSection->id)->count())->toBe(1);

    expect(Unit::find($unit->id))->not->toBeNull()
        ->and(Unit::where('section_id', $homeSection->id)->count())->toBe(1);
});

test('updating canonical unit title syncs to mirror', function () {
    ['philosophy' => $philosophy, 'targetSection' => $targetSection, 'unit' => $unit] = createUnitLinkTestCurriculum('sync-c');

    app(UnitCloneService::class)->cloneUnitTreeToSection($unit, $targetSection);

    $unit->update(['title' => 'Updated Canonical Title']);

    $mirror = Unit::query()
        ->where('cloned_from_unit_id', $unit->id)
        ->where('section_id', $targetSection->id)
        ->first();

    expect($mirror->fresh()->title)->toBe('Updated Canonical Title');
});

test('updating mirror unit title syncs to canonical', function () {
    ['targetSection' => $targetSection, 'unit' => $unit] = createUnitLinkTestCurriculum('sync-m');

    app(UnitCloneService::class)->cloneUnitTreeToSection($unit, $targetSection);

    $mirror = Unit::query()
        ->where('cloned_from_unit_id', $unit->id)
        ->where('section_id', $targetSection->id)
        ->first();

    $mirror->update(['title' => 'Updated Mirror Title']);

    expect($unit->fresh()->title)->toBe('Updated Mirror Title');
});

test('deleting canonical unit keeps mirror in linked section', function () {
    ['targetSection' => $targetSection, 'unit' => $unit] = createUnitLinkTestCurriculum('del-c');

    app(UnitCloneService::class)->cloneUnitTreeToSection($unit, $targetSection);

    $admin = unitLinkAdmin();

    $this->actingAs($admin)->delete(route('admin.units.destroy', $unit));

    expect(Unit::withTrashed()->find($unit->id)?->trashed())->toBeTrue();

    $mirror = Unit::query()
        ->where('cloned_from_unit_id', $unit->id)
        ->where('section_id', $targetSection->id)
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->trashed())->toBeFalse()
        ->and(Lesson::where('unit_id', $mirror->id)->count())->toBe(1);
});

test('removing link soft deletes mirror only', function () {
    ['targetSection' => $targetSection, 'unit' => $unit] = createUnitLinkTestCurriculum('unlink');

    app(UnitCloneService::class)->cloneUnitTreeToSection($unit, $targetSection);

    $admin = unitLinkAdmin();

    $this->actingAs($admin)->put(route('admin.units.update', $unit), [
        'title' => $unit->title,
        'order' => $unit->order,
        'is_active' => true,
        'sync_mirrored_sections' => true,
        'linked_section_ids' => [],
    ]);

    $mirror = Unit::withTrashed()
        ->where('cloned_from_unit_id', $unit->id)
        ->where('section_id', $targetSection->id)
        ->first();

    expect($mirror)->not->toBeNull()
        ->and($mirror->trashed())->toBeTrue();

    expect(Unit::find($unit->id))->not->toBeNull()
        ->and(Unit::find($unit->id)->trashed())->toBeFalse();
});
