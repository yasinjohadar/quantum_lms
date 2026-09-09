<?php

namespace Tests\Feature;

use App\Models\ClassEnrollment;
use App\Models\Currency;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\User;
use App\Services\AdminStudentEnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewSubjectProvisionsExistingClassStudentsTest extends TestCase
{
    use RefreshDatabase;

    protected Stage $stage;

    protected Currency $currency;

    protected SchoolClass $class;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create();
        $this->stage = Stage::create(['name' => 'Provision Stage', 'order' => 1]);
        $this->currency = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_active' => true,
            'order' => 1,
        ]);
        $this->class = SchoolClass::create([
            'name' => 'Provision Class',
            'slug' => 'provision-class-'.uniqid(),
            'stage_id' => $this->stage->id,
            'is_active' => true,
            'is_free' => true,
            'price' => 0,
            'default_currency_id' => $this->currency->id,
        ]);
    }

    protected function makeSubject(array $attributes = []): Subject
    {
        return Subject::create(array_merge([
            'name' => 'New Subject',
            'slug' => 'new-subject-'.uniqid(),
            'class_id' => $this->class->id,
            'is_active' => true,
            'is_free' => true,
            'price' => 0,
            'default_currency_id' => $this->currency->id,
            'pricing_mode' => 'inherit',
        ], $attributes));
    }

    protected function enrollInClass(User $user, string $status = 'approved'): ClassEnrollment
    {
        return ClassEnrollment::create([
            'user_id' => $user->id,
            'class_id' => $this->class->id,
            'status' => $status,
            'enrolled_by' => $this->staff->id,
            'enrolled_at' => now(),
        ]);
    }

    public function test_approved_class_students_are_enrolled_in_newly_added_subject(): void
    {
        $ahmed = User::factory()->create();
        $mohammed = User::factory()->create();
        $sara = User::factory()->create();
        $khaled = User::factory()->create();
        $ali = User::factory()->create();

        foreach ([$ahmed, $mohammed, $sara, $khaled] as $approvedStudent) {
            $this->enrollInClass($approvedStudent, 'approved');
        }
        $this->enrollInClass($ali, 'pending');

        $subject = $this->makeSubject(['name' => 'Mathematics']);

        app(AdminStudentEnrollmentService::class)
            ->provisionSubjectForAlreadyEnrolledClassStudents($subject, $this->staff->id);

        foreach ([$ahmed, $mohammed, $sara, $khaled] as $approvedStudent) {
            $this->assertDatabaseHas('enrollments', [
                'user_id' => $approvedStudent->id,
                'subject_id' => $subject->id,
                'status' => 'active',
            ]);
        }

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $ali->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_provisioning_is_idempotent_and_creates_no_duplicates(): void
    {
        $student = User::factory()->create();
        $this->enrollInClass($student, 'approved');

        $subject = $this->makeSubject();

        $service = app(AdminStudentEnrollmentService::class);
        $service->provisionSubjectForAlreadyEnrolledClassStudents($subject, $this->staff->id);
        $service->provisionSubjectForAlreadyEnrolledClassStudents($subject, $this->staff->id);

        $this->assertSame(1, Enrollment::where('user_id', $student->id)
            ->where('subject_id', $subject->id)
            ->count());
    }

    public function test_existing_enrollment_is_left_completely_untouched(): void
    {
        $student = User::factory()->create();
        $this->enrollInClass($student, 'approved');

        $subject = $this->makeSubject();

        $existing = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'enrolled_by' => null,
            'enrolled_at' => now()->subDays(10),
            'status' => 'suspended',
            'notes' => 'ملاحظة قديمة يجب ألا تتغيّر',
        ]);

        app(AdminStudentEnrollmentService::class)
            ->provisionSubjectForAlreadyEnrolledClassStudents($subject, $this->staff->id);

        $existing->refresh();
        $this->assertSame('suspended', $existing->status);
        $this->assertSame('ملاحظة قديمة يجب ألا تتغيّر', $existing->notes);
        $this->assertSame(1, Enrollment::where('user_id', $student->id)
            ->where('subject_id', $subject->id)
            ->count());
    }

    public function test_soft_deleted_enrollment_is_not_recreated_or_restored(): void
    {
        $student = User::factory()->create();
        $this->enrollInClass($student, 'approved');

        $subject = $this->makeSubject();

        $trashed = Enrollment::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'enrolled_by' => null,
            'enrolled_at' => now()->subDays(30),
            'status' => 'active',
            'notes' => 'ألغي يدوياً',
        ]);
        $trashed->delete();

        app(AdminStudentEnrollmentService::class)
            ->provisionSubjectForAlreadyEnrolledClassStudents($subject, $this->staff->id);

        $this->assertTrue($trashed->fresh()->trashed());
        $this->assertSame(0, Enrollment::where('user_id', $student->id)
            ->where('subject_id', $subject->id)
            ->count());
        $this->assertSame(1, Enrollment::withTrashed()
            ->where('user_id', $student->id)
            ->where('subject_id', $subject->id)
            ->count());
    }

    public function test_subject_creation_via_controller_provisions_existing_students(): void
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped('Admin permission/role stack requires MySQL migrations.');
        }

        $adminRole = \App\Models\Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['dashboard_type' => 'admin', 'staff_profile' => 'none']
        );
        \Spatie\Permission\Models\Permission::firstOrCreate(
            ['name' => 'subject-create', 'guard_name' => 'web']
        );

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole($adminRole);
        $admin->givePermissionTo('subject-create');

        $student = User::factory()->create();
        $this->enrollInClass($student, 'approved');

        $response = $this->actingAs($admin)->post(route('admin.subjects.store'), [
            'name' => 'Physics',
            'class_id' => $this->class->id,
            'is_active' => '1',
            'display_in_class' => '1',
            'order' => 0,
            'price' => 0,
            'is_free' => '1',
            'pricing_mode' => 'inherit',
            'default_currency_id' => $this->currency->id,
        ]);

        $response->assertRedirect();

        $subject = Subject::where('class_id', $this->class->id)->where('name', 'Physics')->firstOrFail();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);
    }
}
