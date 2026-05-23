<?php

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
});

function createStudentUnitPageFixture(): array
{
    $suffix = uniqid();
    $student = User::factory()->create();

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
        'is_free' => true,
        'price' => 0,
    ]);

    $subject = Subject::create([
        'name' => 'Subject '.$suffix,
        'slug' => 'subject-'.$suffix,
        'class_id' => $schoolClass->id,
        'order' => 1,
        'is_active' => true,
        'display_in_class' => true,
        'pricing_mode' => 'inherit',
    ]);

    Enrollment::create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'enrolled_by' => 1,
        'enrolled_at' => now(),
        'status' => 'active',
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

    $lesson = Lesson::create([
        'unit_id' => $unit->id,
        'title' => 'Lesson visible '.$suffix,
        'order' => 1,
        'is_active' => true,
        'review_status' => Lesson::REVIEW_STATUS_APPROVED,
    ]);

    $bankQuestion = Question::create([
        'type' => 'single_choice',
        'title' => 'Bank question hidden '.$suffix,
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => $subject->id,
    ]);
    $bankQuestion->units()->sync([$unit->id]);

    $practiceQuestion = Question::create([
        'type' => 'single_choice',
        'title' => 'Unit practice visible '.$suffix,
        'difficulty' => 'medium',
        'default_points' => 1,
        'is_active' => true,
        'subject_id' => null,
    ]);
    $practiceQuestion->units()->sync([$unit->id]);

    return compact('student', 'subject', 'section', 'unit', 'lesson', 'bankQuestion', 'practiceQuestion');
}

test('student unit page hides subject bank questions under lessons', function () {
    [
        'student' => $student,
        'subject' => $subject,
        'section' => $section,
        'unit' => $unit,
        'lesson' => $lesson,
        'bankQuestion' => $bankQuestion,
        'practiceQuestion' => $practiceQuestion,
    ] = createStudentUnitPageFixture();

    $response = $this->actingAs($student)
        ->get(route('student.subjects.folders.unit', [
            'subject' => $subject->id,
            'section' => $section->id,
            'unit' => $unit->id,
        ]));

    $response->assertOk();
    $response->assertSee($lesson->title, false);
    $response->assertDontSee($bankQuestion->title, false);
    $response->assertSee($practiceQuestion->title, false);
});
