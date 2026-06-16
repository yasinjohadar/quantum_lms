<?php

use App\Models\Enrollment;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
    if (! Schema::hasTable('enrollments') || ! Schema::hasTable('users')) {
        $this->markTestSkipped('Database schema not migrated; run migrations on MySQL.');
    }
});

function createLockdownStudent(): User
{
    $role = Role::firstOrCreate(
        ['name' => 'student', 'guard_name' => 'web'],
        ['dashboard_type' => 'student', 'staff_profile' => 'none']
    );

    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole($role);

    return $user;
}

function createLockdownClassWithSubject(): array
{
    $stage = Stage::create([
        'name' => 'Stage '.uniqid(),
        'slug' => 'stage-'.uniqid(),
        'order' => 1,
        'is_active' => true,
    ]);

    $schoolClass = SchoolClass::create([
        'name' => 'Class '.uniqid(),
        'slug' => 'class-'.uniqid(),
        'stage_id' => $stage->id,
        'order' => 1,
        'is_active' => true,
    ]);

    $subject = Subject::create([
        'name' => 'Subject '.uniqid(),
        'slug' => 'subject-'.uniqid(),
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
    ]);

    return compact('schoolClass', 'subject');
}

test('unenrolled student is redirected from dashboard to enrollments', function () {
    $student = createLockdownStudent();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertRedirect(route('student.enrollments.index'))
        ->assertSessionHas('enrollment_required_warning', true);
});

test('unenrolled student is redirected from classes to enrollments', function () {
    $student = createLockdownStudent();

    $this->actingAs($student)
        ->get(route('student.classes'))
        ->assertRedirect(route('student.enrollments.index'));
});

test('unenrolled student can access enrollments page', function () {
    $student = createLockdownStudent();

    $this->actingAs($student)
        ->get(route('student.enrollments.index'))
        ->assertOk()
        ->assertSee('طلب الانضمام للمواد الدراسية', false)
        ->assertSee('لم يتم تسجيلك بعد في أي صف أو مادة', false);
});

test('unenrolled student can reach purchase fragment route without enrollment redirect', function () {
    $student = createLockdownStudent();
    ['schoolClass' => $schoolClass] = createLockdownClassWithSubject();

    $response = $this->actingAs($student)
        ->get(route('student.purchases.prepare-class.fragment', $schoolClass));

    expect($response->isRedirect(route('student.enrollments.index')))->toBeFalse();
});

test('enrolled student can access dashboard', function () {
    $student = createLockdownStudent();
    ['subject' => $subject] = createLockdownClassWithSubject();

    Enrollment::create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
        'enrolled_at' => now(),
    ]);

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk();
});
