<?php

use App\Models\Question;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
});

function questionPackImportAdmin(): User
{
    Permission::firstOrCreate(
        ['name' => 'question-import', 'guard_name' => 'web'],
        ['description' => 'استيراد الأسئلة']
    );

    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );
    $adminRole->givePermissionTo('question-import');

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

function createQuestionPackCurriculum(string $suffix = ''): array
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

    return compact('subject', 'unit', 'schoolClass');
}

test('question pack import single choice creates questions with options', function () {
    $admin = questionPackImportAdmin();
    ['subject' => $subject, 'unit' => $unit, 'schoolClass' => $schoolClass] = createQuestionPackCurriculum('pack-sc');

    $csvPath = base_path('docs/اختبار-أحياء.csv');
    $file = new UploadedFile($csvPath, 'biology.csv', 'text/csv', null, true);

    $response = $this->actingAs($admin)->post(route('admin.questions.question-pack.import'), [
        'file' => $file,
        'format' => 'csv',
        'target_type' => 'single_choice',
        'class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'unit_id' => $unit->id,
    ]);

    $response->assertRedirect(route('admin.subjects.questions.index', $subject->id));
    $response->assertSessionHas('success');

    $count = Question::where('subject_id', $subject->id)->where('type', 'single_choice')->count();
    expect($count)->toBe(5);

    $question = Question::where('subject_id', $subject->id)->where('type', 'single_choice')->first();
    expect($question->options()->count())->toBeGreaterThanOrEqual(2);
    expect($question->units()->pluck('units.id')->all())->toBe([$unit->id]);
});

test('question pack import fill blanks stores blank answers without options', function () {
    $admin = questionPackImportAdmin();
    ['subject' => $subject, 'unit' => $unit, 'schoolClass' => $schoolClass] = createQuestionPackCurriculum('pack-fb');

    $csvPath = base_path('docs/اختبار-أحياء.csv');
    $file = new UploadedFile($csvPath, 'biology.csv', 'text/csv', null, true);

    $response = $this->actingAs($admin)->post(route('admin.questions.question-pack.import'), [
        'file' => $file,
        'format' => 'csv',
        'target_type' => 'fill_blanks',
        'class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'unit_id' => $unit->id,
    ]);

    $response->assertRedirect(route('admin.subjects.questions.index', $subject->id));

    $question = Question::where('subject_id', $subject->id)
        ->where('type', 'fill_blanks')
        ->whereNotNull('blank_answers')
        ->first();

    expect($question)->not->toBeNull();
    expect($question->blank_answers)->toBeArray()->not->toBeEmpty();
    expect($question->options()->count())->toBe(0);
});
