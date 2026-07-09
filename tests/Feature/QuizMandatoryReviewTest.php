<?php

use App\Models\Quiz;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\StaffNotificationService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }

    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
});

function seedQuizReviewPermissions(): void
{
    foreach ([
        'quiz-create',
        'quiz-edit',
        'quiz-submit-for-review',
        'quiz-approve-review',
        'settings-manage',
    ] as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }
}

function createQuizReviewCurriculum(): array
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

    return compact('schoolClass', 'subject');
}

function createQuizUploaderUser(): User
{
    $role = Role::updateOrCreate(
        ['name' => 'teacher-content-uploader', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
    );
    $role->syncPermissions(['quiz-create', 'quiz-edit', 'quiz-submit-for-review']);

    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole($role);

    return $user;
}

function quizReviewPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'اختبار مراجعة',
        'pass_percentage' => 50,
        'grading_method' => 'highest',
        'review_options' => 'immediately',
    ], $overrides);
}

test('mandatory quiz review setting forces uploader create into pending review', function () {
    seedQuizReviewPermissions();
    SystemSetting::set('content_quiz_mandatory_review', '1', 'boolean', 'general');

    ['subject' => $subject] = createQuizReviewCurriculum();
    $uploader = createQuizUploaderUser();
    $uploader->assignedSubjects()->attach($subject->id, [
        'assigned_by' => $uploader->id,
        'assigned_at' => now(),
    ]);

    $response = $this->actingAs($uploader)->post(route('admin.quizzes.store'), array_merge(quizReviewPayload(), [
        'subject_id' => $subject->id,
    ]));

    $quiz = Quiz::where('title', 'اختبار مراجعة')->first();
    expect($quiz)->not->toBeNull();
    expect($quiz->review_status)->toBe(Quiz::REVIEW_STATUS_PENDING);
    expect($quiz->is_active)->toBeFalse();
    expect($quiz->is_published)->toBeFalse();
    expect($quiz->submitted_for_review_at)->not->toBeNull();

    $response->assertRedirect(route('admin.quizzes.import-excel.show', $quiz));
});

test('mandatory quiz review ignores is_active tampering on create', function () {
    seedQuizReviewPermissions();
    SystemSetting::set('content_quiz_mandatory_review', '1', 'boolean', 'general');

    ['subject' => $subject] = createQuizReviewCurriculum();
    $uploader = createQuizUploaderUser();
    $uploader->assignedSubjects()->attach($subject->id, [
        'assigned_by' => $uploader->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($uploader)->post(route('admin.quizzes.store'), array_merge(quizReviewPayload(), [
        'subject_id' => $subject->id,
        'is_active' => '1',
    ]));

    $quiz = Quiz::where('title', 'اختبار مراجعة')->first();
    expect($quiz->review_status)->toBe(Quiz::REVIEW_STATUS_PENDING);
    expect($quiz->is_active)->toBeFalse();
});

test('optional quiz review keeps draft when uploader does not submit', function () {
    seedQuizReviewPermissions();
    SystemSetting::set('content_quiz_mandatory_review', '0', 'boolean', 'general');

    ['subject' => $subject] = createQuizReviewCurriculum();
    $uploader = createQuizUploaderUser();
    $uploader->assignedSubjects()->attach($subject->id, [
        'assigned_by' => $uploader->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($uploader)->post(route('admin.quizzes.store'), array_merge(quizReviewPayload(), [
        'subject_id' => $subject->id,
    ]));

    $quiz = Quiz::where('title', 'اختبار مراجعة')->first();
    expect($quiz->review_status)->toBe(Quiz::REVIEW_STATUS_DRAFT);
    expect($quiz->is_active)->toBeFalse();
    expect($quiz->is_published)->toBeFalse();
});

test('optional quiz review submits when uploader enables switch', function () {
    seedQuizReviewPermissions();
    SystemSetting::set('content_quiz_mandatory_review', '0', 'boolean', 'general');

    ['subject' => $subject] = createQuizReviewCurriculum();
    $uploader = createQuizUploaderUser();
    $uploader->assignedSubjects()->attach($subject->id, [
        'assigned_by' => $uploader->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($uploader)->post(route('admin.quizzes.store'), array_merge(quizReviewPayload(), [
        'subject_id' => $subject->id,
        'is_active' => '1',
    ]));

    $quiz = Quiz::where('title', 'اختبار مراجعة')->first();
    expect($quiz->review_status)->toBe(Quiz::REVIEW_STATUS_PENDING);
    expect($quiz->submitted_for_review_at)->not->toBeNull();
});

test('quiz submitted for review dispatches staff notification on create', function () {
    seedQuizReviewPermissions();
    SystemSetting::set('content_quiz_mandatory_review', '0', 'boolean', 'general');

    ['subject' => $subject] = createQuizReviewCurriculum();
    $uploader = createQuizUploaderUser();
    $uploader->assignedSubjects()->attach($subject->id, [
        'assigned_by' => $uploader->id,
        'assigned_at' => now(),
    ]);

    $mock = Mockery::mock(StaffNotificationService::class);
    $mock->shouldReceive('notifyQuizSubmittedForReview')
        ->once()
        ->withArgs(function (Quiz $quiz, User $submitter) use ($uploader) {
            return $quiz->title === 'اختبار مراجعة'
                && $submitter->id === $uploader->id;
        });
    $this->app->instance(StaffNotificationService::class, $mock);

    $this->actingAs($uploader)->post(route('admin.quizzes.store'), array_merge(quizReviewPayload(), [
        'subject_id' => $subject->id,
        'is_active' => '1',
    ]));
});

test('shouldSubmitQuizForReview uses teacher scope and excludes reviewers', function () {
    seedQuizReviewPermissions();

    $uploader = createQuizUploaderUser();
    expect($uploader->shouldSubmitQuizForReview())->toBeTrue();

    $reviewerRole = Role::firstOrCreate(
        ['name' => 'quiz-reviewer', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $reviewerRole->syncPermissions(['quiz-approve-review', 'quiz-submit-for-review', 'review-queue-list']);

    $reviewer = User::factory()->create(['is_active' => true]);
    $reviewer->assignRole($reviewerRole);

    expect($reviewer->shouldSubmitQuizForReview())->toBeFalse();
    expect($reviewer->canReviewContent())->toBeTrue();
});

test('mandatory quiz review applies to teacher without quiz-submit-for-review permission', function () {
    seedQuizReviewPermissions();
    SystemSetting::set('content_quiz_mandatory_review', '1', 'boolean', 'general');

    ['subject' => $subject] = createQuizReviewCurriculum();

    $role = Role::updateOrCreate(
        ['name' => 'teacher-quiz-only', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
    );
    $role->syncPermissions(['quiz-create', 'quiz-edit']);

    $teacher = User::factory()->create(['is_active' => true]);
    $teacher->assignRole($role);
    $teacher->assignedSubjects()->attach($subject->id, [
        'assigned_by' => $teacher->id,
        'assigned_at' => now(),
    ]);

    expect($teacher->shouldSubmitQuizForReview())->toBeTrue();

    $this->actingAs($teacher)->post(route('admin.quizzes.store'), array_merge(quizReviewPayload([
        'title' => 'اختبار بدون صلاحية إرسال',
    ]), [
        'subject_id' => $subject->id,
        'is_active' => '1',
    ]));

    $quiz = Quiz::where('title', 'اختبار بدون صلاحية إرسال')->first();
    expect($quiz)->not->toBeNull();
    expect($quiz->review_status)->toBe(Quiz::REVIEW_STATUS_PENDING);
    expect($quiz->is_active)->toBeFalse();
    expect($quiz->is_published)->toBeFalse();
});

test('mandatory quiz review applies to user with quiz-create without teacher staff profile', function () {
    seedQuizReviewPermissions();
    SystemSetting::set('content_quiz_mandatory_review', '1', 'boolean', 'general');

    ['subject' => $subject] = createQuizReviewCurriculum();

    $role = Role::updateOrCreate(
        ['name' => 'quiz-creator-general', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $role->syncPermissions(['quiz-create', 'quiz-edit']);

    $creator = User::factory()->create(['is_active' => true]);
    $creator->assignRole($role);
    $creator->assignedSubjects()->attach($subject->id, [
        'assigned_by' => $creator->id,
        'assigned_at' => now(),
    ]);

    expect($creator->shouldSubmitQuizForReview())->toBeTrue();

    $this->actingAs($creator)->post(route('admin.quizzes.store'), array_merge(quizReviewPayload([
        'title' => 'اختبار بصلاحية عامة',
    ]), [
        'subject_id' => $subject->id,
        'is_active' => '1',
    ]));

    $quiz = Quiz::where('title', 'اختبار بصلاحية عامة')->first();
    expect($quiz)->not->toBeNull();
    expect($quiz->review_status)->toBe(Quiz::REVIEW_STATUS_PENDING);
    expect($quiz->is_active)->toBeFalse();
    expect($quiz->is_published)->toBeFalse();
});

test('mandatory quiz review applies even when uploader role includes review permissions', function () {
    seedQuizReviewPermissions();
    SystemSetting::set('content_quiz_mandatory_review', '1', 'boolean', 'general');

    ['subject' => $subject] = createQuizReviewCurriculum();

    $role = Role::updateOrCreate(
        ['name' => 'teacher-with-review-perms', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'teacher']
    );
    $role->syncPermissions([
        'quiz-create',
        'quiz-edit',
        'quiz-approve-review',
        'review-queue-list',
    ]);

    $teacher = User::factory()->create(['is_active' => true]);
    $teacher->assignRole($role);
    $teacher->assignedSubjects()->attach($subject->id, [
        'assigned_by' => $teacher->id,
        'assigned_at' => now(),
    ]);

    expect($teacher->canReviewContent())->toBeTrue();
    expect($teacher->shouldSubmitQuizForReview())->toBeTrue();

    $this->actingAs($teacher)->post(route('admin.quizzes.store'), array_merge(quizReviewPayload([
        'title' => 'اختبار مع صلاحيات مراجعة',
    ]), [
        'subject_id' => $subject->id,
        'is_active' => '1',
    ]));

    $quiz = Quiz::where('title', 'اختبار مع صلاحيات مراجعة')->first();
    expect($quiz)->not->toBeNull();
    expect($quiz->review_status)->toBe(Quiz::REVIEW_STATUS_PENDING);
    expect($quiz->is_active)->toBeFalse();
    expect($quiz->is_published)->toBeFalse();
});

test('admin can toggle mandatory quiz review setting', function () {
    seedQuizReviewPermissions();
    SystemSetting::set('content_quiz_mandatory_review', '0', 'boolean', 'general');

    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $adminRole->syncPermissions(['settings-manage']);

    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole($adminRole);

    $this->actingAs($admin)->put(route('admin.settings.update'), [
        'group' => 'general',
        'settings' => [
            'content_quiz_mandatory_review' => '1',
        ],
    ])->assertRedirect(route('admin.settings.index', ['group' => 'general']));

    expect(SystemSetting::quizMandatoryReviewEnabled())->toBeTrue();
});
