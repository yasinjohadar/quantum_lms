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

function nerveTestImportAdmin(): User
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

function createNerveTestCurriculum(string $suffix = ''): array
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

test('nerve test import links questions to subject and unit from csv', function () {
    $admin = nerveTestImportAdmin();
    ['subject' => $subject, 'unit' => $unit, 'schoolClass' => $schoolClass] = createNerveTestCurriculum('nerve');

    $csvPath = base_path('docs/اختبار-الأعصاب.csv');
    expect(file_exists($csvPath))->toBeTrue();

    $file = new UploadedFile(
        $csvPath,
        'nerve-test.csv',
        'text/csv',
        null,
        true
    );

    $response = $this->actingAs($admin)->post(route('admin.questions.nerve-test.import'), [
        'file' => $file,
        'format' => 'csv',
        'class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'unit_id' => $unit->id,
    ]);

    $response->assertRedirect(route('admin.subjects.questions.index', $subject->id));
    $response->assertSessionHas('success');

    $count = Question::where('subject_id', $subject->id)->where('type', 'true_false')->count();
    expect($count)->toBe(5);

    $first = Question::where('subject_id', $subject->id)->where('type', 'true_false')->oldest('id')->first();
    expect($first)->not->toBeNull();
    expect($first->units()->pluck('units.id')->all())->toBe([$unit->id]);
});
