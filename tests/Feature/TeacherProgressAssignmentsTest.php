<?php

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
});

function seedProgressAssignmentPermissions(): void
{
    foreach ([
        'teacher-progress-view',
        'teacher-assignment-update',
        'teacher-assignment-manage-classes',
        'teacher-assignment-manage-subjects',
    ] as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }
}

test('attach and detach class updates teacher_classes pivot', function () {
    seedProgressAssignmentPermissions();

    $adminRole = Role::firstOrCreate(
        ['name' => 'progress-assign-admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $adminRole->syncPermissions([
        'teacher-progress-view',
        'teacher-assignment-update',
        'teacher-assignment-manage-classes',
    ]);

    $teacherRole = Role::updateOrCreate(
        ['name' => 'teacher', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $teacherUser = User::factory()->create();
    $teacherUser->assignRole($teacherRole);

    $stage = Stage::create([
        'name' => 'Stage T',
        'slug' => 'stage-t-'.uniqid(),
        'order' => 1,
        'is_active' => true,
    ]);

    $classA = SchoolClass::create([
        'name' => 'Class A',
        'slug' => 'class-a-'.uniqid(),
        'stage_id' => $stage->id,
        'order' => 1,
        'is_active' => true,
    ]);

    $classB = SchoolClass::create([
        'name' => 'Class B',
        'slug' => 'class-b-'.uniqid(),
        'stage_id' => $stage->id,
        'order' => 2,
        'is_active' => true,
    ]);

    $teacherUser->assignedClasses()->attach($classA->id, [
        'assigned_by' => $admin->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($admin)->post(route('admin.teachers.assignments.attach-class', $teacherUser), [
        'class_id' => $classB->id,
    ])->assertRedirect(route('admin.teachers.progress.show', $teacherUser));

    $this->assertDatabaseHas('teacher_classes', [
        'teacher_id' => $teacherUser->id,
        'class_id' => $classB->id,
    ]);

    $this->actingAs($admin)->delete(route('admin.teachers.assignments.detach-class', [$teacherUser, $classB]))
        ->assertRedirect(route('admin.teachers.progress.show', $teacherUser));

    $this->assertDatabaseMissing('teacher_classes', [
        'teacher_id' => $teacherUser->id,
        'class_id' => $classB->id,
    ]);
});

test('attach and detach subject updates teacher_subjects pivot', function () {
    seedProgressAssignmentPermissions();

    $adminRole = Role::firstOrCreate(
        ['name' => 'progress-assign-admin-2', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $adminRole->syncPermissions([
        'teacher-progress-view',
        'teacher-assignment-update',
        'teacher-assignment-manage-subjects',
    ]);

    $teacherRole = Role::updateOrCreate(
        ['name' => 'teacher', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $teacherUser = User::factory()->create();
    $teacherUser->assignRole($teacherRole);

    $stage = Stage::create([
        'name' => 'Stage S',
        'slug' => 'stage-s-'.uniqid(),
        'order' => 1,
        'is_active' => true,
    ]);

    $schoolClass = SchoolClass::create([
        'name' => 'Class S',
        'slug' => 'class-s-'.uniqid(),
        'stage_id' => $stage->id,
        'order' => 1,
        'is_active' => true,
    ]);

    $subjectA = Subject::create([
        'name' => 'Subject A',
        'slug' => 'subj-a-'.uniqid(),
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $subjectB = Subject::create([
        'name' => 'Subject B',
        'slug' => 'subj-b-'.uniqid(),
        'class_id' => $schoolClass->id,
        'order' => 2,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $teacherUser->assignedSubjects()->attach($subjectA->id, [
        'assigned_by' => $admin->id,
        'assigned_at' => now(),
        'required_pages' => null,
    ]);

    $this->actingAs($admin)->post(route('admin.teachers.assignments.attach-subject', $teacherUser), [
        'subject_id' => $subjectB->id,
        'required_pages' => 10,
    ])->assertRedirect(route('admin.teachers.progress.show', $teacherUser));

    $this->assertDatabaseHas('teacher_subjects', [
        'teacher_id' => $teacherUser->id,
        'subject_id' => $subjectB->id,
    ]);

    $this->actingAs($admin)->delete(route('admin.teachers.assignments.detach-subject', [$teacherUser, $subjectB]))
        ->assertRedirect(route('admin.teachers.progress.show', $teacherUser));

    $this->assertDatabaseMissing('teacher_subjects', [
        'teacher_id' => $teacherUser->id,
        'subject_id' => $subjectB->id,
    ]);
});

test('attach class is forbidden without teacher-assignment-update permission', function () {
    seedProgressAssignmentPermissions();

    $limitedRole = Role::firstOrCreate(
        ['name' => 'progress-view-only', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $limitedRole->syncPermissions([
        'teacher-progress-view',
        'teacher-assignment-manage-classes',
    ]);

    $teacherRole = Role::updateOrCreate(
        ['name' => 'teacher', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
    );

    $viewer = User::factory()->create();
    $viewer->assignRole($limitedRole);

    $teacherUser = User::factory()->create();
    $teacherUser->assignRole($teacherRole);

    $stage = Stage::create([
        'name' => 'Stage X',
        'slug' => 'stage-x-'.uniqid(),
        'order' => 1,
        'is_active' => true,
    ]);

    $class = SchoolClass::create([
        'name' => 'Class X',
        'slug' => 'class-x-'.uniqid(),
        'stage_id' => $stage->id,
        'order' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($viewer)->post(route('admin.teachers.assignments.attach-class', $teacherUser), [
        'class_id' => $class->id,
    ])->assertForbidden();
});

test('assignments update with Accept JSON returns payload and persists', function () {
    seedProgressAssignmentPermissions();

    $adminRole = Role::firstOrCreate(
        ['name' => 'progress-assign-json', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $adminRole->syncPermissions([
        'teacher-progress-view',
        'teacher-assignment-update',
        'teacher-assignment-manage-classes',
        'teacher-assignment-manage-subjects',
    ]);

    $teacherRole = Role::updateOrCreate(
        ['name' => 'teacher', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $teacherUser = User::factory()->create();
    $teacherUser->assignRole($teacherRole);

    $stage = Stage::create([
        'name' => 'Stage J',
        'slug' => 'stage-j-'.uniqid(),
        'order' => 1,
        'is_active' => true,
    ]);

    $schoolClass = SchoolClass::create([
        'name' => 'Class J',
        'slug' => 'class-j-'.uniqid(),
        'stage_id' => $stage->id,
        'order' => 1,
        'is_active' => true,
    ]);

    $subjectA = Subject::create([
        'name' => 'Subject J1',
        'slug' => 'subj-j1-'.uniqid(),
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $subjectB = Subject::create([
        'name' => 'Subject J2',
        'slug' => 'subj-j2-'.uniqid(),
        'class_id' => $schoolClass->id,
        'order' => 2,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $response = $this->actingAs($admin)->putJson(route('admin.teachers.assignments.update', $teacherUser), [
        'classes' => [(string) $schoolClass->id],
        'subjects' => [(string) $subjectA->id],
        'required_pages' => [
            (string) $subjectA->id => '7',
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('assigned_classes_count', 1)
        ->assertJsonPath('assigned_subjects_count', 1)
        ->assertJsonStructure([
            'assigned_class_ids',
            'assigned_subject_ids',
            'required_pages',
            'html' => ['progress_card', 'side_panel', 'indep_body'],
        ]);

    $this->assertDatabaseHas('teacher_classes', [
        'teacher_id' => $teacherUser->id,
        'class_id' => $schoolClass->id,
    ]);
    $this->assertDatabaseHas('teacher_subjects', [
        'teacher_id' => $teacherUser->id,
        'subject_id' => $subjectA->id,
    ]);
    $this->assertDatabaseMissing('teacher_subjects', [
        'teacher_id' => $teacherUser->id,
        'subject_id' => $subjectB->id,
    ]);

    $teacherUser->refresh();
    expect((int) $teacherUser->assignedSubjects()->where('subjects.id', $subjectA->id)->first()->pivot->required_pages)->toBe(7);
});

test('assignments JSON persists subject even when its class is not among selected classes', function () {
    seedProgressAssignmentPermissions();

    $adminRole = Role::firstOrCreate(
        ['name' => 'progress-assign-json-cross', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $adminRole->syncPermissions([
        'teacher-progress-view',
        'teacher-assignment-update',
        'teacher-assignment-manage-classes',
        'teacher-assignment-manage-subjects',
    ]);

    $teacherRole = Role::updateOrCreate(
        ['name' => 'teacher', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $teacherUser = User::factory()->create();
    $teacherUser->assignRole($teacherRole);

    $stage = Stage::create([
        'name' => 'Stage Cross',
        'slug' => 'stage-cross-'.uniqid(),
        'order' => 1,
        'is_active' => true,
    ]);

    $classA = SchoolClass::create([
        'name' => 'Class Cross A',
        'slug' => 'class-cross-a-'.uniqid(),
        'stage_id' => $stage->id,
        'order' => 1,
        'is_active' => true,
    ]);

    $classB = SchoolClass::create([
        'name' => 'Class Cross B',
        'slug' => 'class-cross-b-'.uniqid(),
        'stage_id' => $stage->id,
        'order' => 2,
        'is_active' => true,
    ]);

    $subjectInB = Subject::create([
        'name' => 'Subject in B',
        'slug' => 'subj-cross-b-'.uniqid(),
        'class_id' => $classB->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $response = $this->actingAs($admin)->putJson(route('admin.teachers.assignments.update', $teacherUser), [
        'classes' => [(string) $classA->id],
        'subjects' => [(string) $subjectInB->id],
        'required_pages' => [
            (string) $subjectInB->id => '3',
        ],
    ]);

    $response->assertOk()->assertJsonPath('ok', true);

    $this->assertDatabaseHas('teacher_subjects', [
        'teacher_id' => $teacherUser->id,
        'subject_id' => $subjectInB->id,
    ]);

    $teacherUser->refresh();
    expect((int) $teacherUser->assignedSubjects()->where('subjects.id', $subjectInB->id)->first()->pivot->required_pages)->toBe(3);
});

test('patch subject required pages updates pivot and returns json summary', function () {
    seedProgressAssignmentPermissions();

    $adminRole = Role::firstOrCreate(
        ['name' => 'progress-assign-patch-rp', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $adminRole->syncPermissions([
        'teacher-progress-view',
        'teacher-assignment-update',
        'teacher-assignment-manage-subjects',
    ]);

    $teacherRole = Role::updateOrCreate(
        ['name' => 'teacher', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $teacherUser = User::factory()->create();
    $teacherUser->assignRole($teacherRole);

    $stage = Stage::create([
        'name' => 'Stage RP',
        'slug' => 'stage-rp-'.uniqid(),
        'order' => 1,
        'is_active' => true,
    ]);

    $schoolClass = SchoolClass::create([
        'name' => 'Class RP',
        'slug' => 'class-rp-'.uniqid(),
        'stage_id' => $stage->id,
        'order' => 1,
        'is_active' => true,
    ]);

    $subject = Subject::create([
        'name' => 'Subject RP',
        'slug' => 'subj-rp-'.uniqid(),
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    $teacherUser->assignedSubjects()->attach($subject->id, [
        'assigned_by' => $admin->id,
        'assigned_at' => now(),
        'required_pages' => 10,
    ]);

    $response = $this->actingAs($admin)->patchJson(
        route('admin.teachers.assignments.subject-required-pages', [$teacherUser, $subject]),
        ['required_pages' => 200]
    );

    $response->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('required_pages', 200)
        ->assertJsonStructure(['summary' => ['total_pages_required', 'total_pages_completed', 'total_pages_percentage']]);

    expect((int) $teacherUser->fresh()->assignedSubjects()->where('subjects.id', $subject->id)->first()->pivot->required_pages)->toBe(200);
});
