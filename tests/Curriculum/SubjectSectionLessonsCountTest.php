<?php

use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
    if (! Schema::hasTable('subject_sections') || ! Schema::hasTable('lessons')) {
        $this->markTestSkipped('Database schema not migrated; run migrations on MySQL.');
    }
});

function createLessonsCountFixture(): array
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

    $section = SubjectSection::create([
        'subject_id' => $subject->id,
        'title' => 'Section '.$suffix,
        'order' => 0,
        'is_active' => true,
        'type' => SubjectSection::TYPE_LESSONS,
    ]);

    return compact('subject', 'section');
}

test('countAllLessonsForDisplay returns zero for empty section', function () {
    ['section' => $section] = createLessonsCountFixture();

    $loaded = SubjectSection::with(['directLessons', 'units.lessons', 'units.linkedLessons', 'mirroredUnits'])
        ->findOrFail($section->id);

    expect($loaded->countAllLessonsForDisplay())->toBe(0);
});

test('countAllLessonsForDisplay includes direct lessons and unit subtree without duplicates', function () {
    ['section' => $section] = createLessonsCountFixture();

    $rootUnit = Unit::create([
        'section_id' => $section->id,
        'title' => 'Root unit',
        'order' => 0,
        'is_active' => true,
    ]);

    $childUnit = Unit::create([
        'section_id' => $section->id,
        'parent_id' => $rootUnit->id,
        'title' => 'Child unit',
        'order' => 1,
        'is_active' => true,
    ]);

    Lesson::create([
        'section_id' => $section->id,
        'title' => 'Direct lesson',
        'order' => 0,
        'is_active' => true,
    ]);

    Lesson::create([
        'section_id' => $section->id,
        'unit_id' => $rootUnit->id,
        'title' => 'Root lesson',
        'order' => 0,
        'is_active' => true,
    ]);

    Lesson::create([
        'section_id' => $section->id,
        'unit_id' => $childUnit->id,
        'title' => 'Child lesson',
        'order' => 0,
        'is_active' => true,
    ]);

    $linkedLesson = Lesson::create([
        'section_id' => $section->id,
        'unit_id' => $childUnit->id,
        'title' => 'Linked lesson',
        'order' => 1,
        'is_active' => true,
    ]);

    $rootUnit->linkedLessons()->attach($linkedLesson->id);

    $loaded = SubjectSection::with([
        'directLessons',
        'units.lessons',
        'units.linkedLessons',
        'mirroredUnits.lessons',
        'mirroredUnits.linkedLessons',
    ])->findOrFail($section->id);

    expect($loaded->countAllLessonsForDisplay())->toBe(4);
});
