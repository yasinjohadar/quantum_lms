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

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
});

function importCurriculumAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

function createImportCurriculum(string $suffix = ''): array
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

test('import links questions to subject and unit from form', function () {
    $admin = importCurriculumAdmin();
    ['subject' => $subject, 'unit' => $unit, 'schoolClass' => $schoolClass] = createImportCurriculum('import');

    $csvContent = implode("\n", [
        'type,title,difficulty,points,option1,option1_correct,option2,option2_correct',
        'single_choice,Imported question,medium,1,Yes,1,No,0',
    ]);

    $file = UploadedFile::fake()->createWithContent('questions.csv', $csvContent);

    $columnMapping = json_encode([
        'type' => 'type',
        'title' => 'title',
        'difficulty' => 'difficulty',
        'points' => 'points',
        'option1' => 'option1',
        'option1_correct' => 'option1_correct',
        'option2' => 'option2',
        'option2_correct' => 'option2_correct',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.questions.import'), [
        'file' => $file,
        'column_mapping' => $columnMapping,
        'class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'unit_id' => $unit->id,
    ]);

    $response->assertRedirect(route('admin.subjects.questions.index', $subject->id));
    $response->assertSessionHas('success');

    $question = Question::where('title', 'Imported question')->first();

    expect($question)->not->toBeNull();
    expect($question->subject_id)->toBe($subject->id);
    expect($question->units()->pluck('units.id')->all())->toBe([$unit->id]);
});
