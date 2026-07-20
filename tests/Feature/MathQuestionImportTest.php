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

function mathImportAdmin(): User
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

function createMathImportCurriculum(string $suffix = ''): array
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

test('math csv parse endpoint returns rendered katex preview', function () {
    $admin = mathImportAdmin();

    $csvPath = base_path('docs/اختبار-رياضيات.csv');
    $file = new UploadedFile($csvPath, 'math.csv', 'text/csv', null, true);

    $response = $this->actingAs($admin)->post(route('admin.questions.math.parse'), [
        'file' => $file,
        'format' => 'csv',
    ]);

    $response->assertOk();
    $data = $response->json();

    expect($data['count'])->toBe(30);
    expect($data['questions'][0]['title_html'])->toContain('katex-src');
    expect($data['questions'][0]['options'])->toHaveCount(4);
    expect(collect($data['questions'][0]['options'])->firstWhere('is_correct', true)['letter'])->toBe('C');
});

test('math csv import creates single choice questions with combined hint and rationale', function () {
    $admin = mathImportAdmin();
    ['subject' => $subject, 'unit' => $unit, 'schoolClass' => $schoolClass] = createMathImportCurriculum('math-import');

    $csvPath = base_path('docs/اختبار-رياضيات.csv');
    $file = new UploadedFile($csvPath, 'math.csv', 'text/csv', null, true);

    $response = $this->actingAs($admin)->post(route('admin.questions.math.import'), [
        'file' => $file,
        'format' => 'csv',
        'class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'unit_id' => $unit->id,
    ]);

    $response->assertRedirect(route('admin.subjects.questions.index', $subject->id));
    $response->assertSessionHas('success');

    $questions = Question::where('subject_id', $subject->id)->where('type', 'single_choice')->orderBy('id')->get();
    expect($questions)->toHaveCount(30);

    $first = $questions->first();
    expect($first->options()->count())->toBe(4);
    expect($first->options()->where('is_correct', true)->count())->toBe(1);
    expect($first->explanation)->toContain('تلميح:');
    expect($first->explanation)->toContain('التفسير:');
    expect($first->units()->pluck('units.id')->all())->toBe([$unit->id]);

    // اللاتكس يجب أن يكون معالجاً (لا رموز يونيكود خام متبقية للأس/الدليل)
    expect($first->title)->toContain('$');
    expect($first->title)->not->toContain('ₙ');
});
