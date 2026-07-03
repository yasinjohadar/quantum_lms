<?php

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
});

function quizExcelImportAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

function quizExcelImportCurriculum(): array
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

function quizExcelImportCsv(): UploadedFile
{
    $csvContent = implode("\n", [
        'type,title,difficulty,points,option1,option1_correct,option2,option2_correct',
        'single_choice,Quiz import question,medium,2,Yes,1,No,0',
    ]);

    return UploadedFile::fake()->createWithContent('quiz-questions.csv', $csvContent);
}

function quizExcelColumnMapping(): string
{
    return json_encode([
        'type' => 'type',
        'title' => 'title',
        'difficulty' => 'difficulty',
        'points' => 'points',
        'option1' => 'option1',
        'option1_correct' => 'option1_correct',
        'option2' => 'option2',
        'option2_correct' => 'option2_correct',
    ]);
}

test('creating quiz redirects to import excel step', function () {
    $admin = quizExcelImportAdmin();
    ['subject' => $subject] = quizExcelImportCurriculum();

    $response = $this->actingAs($admin)->post(route('admin.quizzes.store'), [
        'title' => 'Quiz with excel step',
        'subject_id' => $subject->id,
        'pass_percentage' => 50,
        'grading_method' => 'highest',
        'review_options' => 'immediately',
    ]);

    $quiz = Quiz::where('title', 'Quiz with excel step')->first();
    expect($quiz)->not->toBeNull();

    $response->assertRedirect(route('admin.quizzes.import-excel.show', $quiz));
});

test('import excel show page loads for quiz with subject', function () {
    $admin = quizExcelImportAdmin();
    ['subject' => $subject] = quizExcelImportCurriculum();

    $quiz = Quiz::create([
        'title' => 'Import page quiz',
        'subject_id' => $subject->id,
        'pass_percentage' => 50,
        'grading_method' => 'highest',
        'review_options' => 'immediately',
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.quizzes.import-excel.show', $quiz));

    $response->assertOk();
    $response->assertSee('استيراد أسئلة Excel');
    $response->assertSee('تخطي — إدارة الأسئلة يدوياً');
});

test('import excel attaches questions to quiz', function () {
    $admin = quizExcelImportAdmin();
    ['subject' => $subject, 'schoolClass' => $schoolClass] = quizExcelImportCurriculum();

    $quiz = Quiz::create([
        'title' => 'Quiz for import',
        'subject_id' => $subject->id,
        'pass_percentage' => 50,
        'grading_method' => 'highest',
        'review_options' => 'immediately',
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.quizzes.import-excel.store', $quiz), [
        'file' => quizExcelImportCsv(),
        'column_mapping' => quizExcelColumnMapping(),
        'class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
    ]);

    $response->assertRedirect(route('admin.quizzes.questions', $quiz));
    $response->assertSessionHas('success');

    $question = Question::where('title', 'Quiz import question')->first();
    expect($question)->not->toBeNull();
    expect($question->subject_id)->toBe($subject->id);

    expect(QuizQuestion::where('quiz_id', $quiz->id)->where('question_id', $question->id)->exists())->toBeTrue();
});

test('import excel without subject shows warning on import page', function () {
    $admin = quizExcelImportAdmin();

    $quiz = Quiz::create([
        'title' => 'Quiz without subject',
        'pass_percentage' => 50,
        'grading_method' => 'highest',
        'review_options' => 'immediately',
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.quizzes.import-excel.show', $quiz));

    $response->assertOk();
    $response->assertSee('يجب تحديد');
});

test('skip link on import page goes to quiz questions', function () {
    $admin = quizExcelImportAdmin();
    ['subject' => $subject] = quizExcelImportCurriculum();

    $quiz = Quiz::create([
        'title' => 'Skip import quiz',
        'subject_id' => $subject->id,
        'pass_percentage' => 50,
        'grading_method' => 'highest',
        'review_options' => 'immediately',
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.quizzes.import-excel.show', $quiz));

    $response->assertOk();
    $response->assertSee(route('admin.quizzes.questions', $quiz), false);
});
