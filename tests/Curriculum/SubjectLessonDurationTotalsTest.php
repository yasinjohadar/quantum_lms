<?php

use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Support\LessonDurationFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
    if (! Schema::hasTable('lessons') || ! Schema::hasTable('subject_sections')) {
        $this->markTestSkipped('Database schema not migrated; run migrations on MySQL.');
    }
});

function createDurationFixture(): array
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

test('LessonDurationFormatter formats hours and minutes', function () {
    expect(LessonDurationFormatter::formatHoursMinutes(0))->toBe('0 د');
    expect(LessonDurationFormatter::formatHoursMinutes(2700))->toBe('45 د');
    expect(LessonDurationFormatter::formatHoursMinutes(6300))->toBe('1 س 45 د');
    expect(LessonDurationFormatter::formatHoursMinutes(3600))->toBe('1 س');
});

test('unit totalLessonsDurationSecondsForDisplay sums unit lessons', function () {
    ['section' => $section] = createDurationFixture();

    $unit = Unit::create([
        'section_id' => $section->id,
        'title' => 'Unit',
        'order' => 0,
        'is_active' => true,
    ]);

    Lesson::create([
        'section_id' => $section->id,
        'unit_id' => $unit->id,
        'title' => 'Lesson A',
        'order' => 0,
        'duration' => 600,
        'is_active' => true,
    ]);

    Lesson::create([
        'section_id' => $section->id,
        'unit_id' => $unit->id,
        'title' => 'Lesson B',
        'order' => 1,
        'duration' => 1200,
        'is_active' => true,
    ]);

    $loaded = Unit::with(['lessons', 'linkedLessons'])->findOrFail($unit->id);

    expect($loaded->totalLessonsDurationSecondsForDisplay())->toBe(1800);
});

test('section totalLessonsDurationSecondsForDisplay includes direct and unit lessons without duplicate', function () {
    ['section' => $section] = createDurationFixture();

    Lesson::create([
        'section_id' => $section->id,
        'title' => 'Direct lesson',
        'order' => 0,
        'duration' => 300,
        'is_active' => true,
    ]);

    $unit = Unit::create([
        'section_id' => $section->id,
        'title' => 'Unit',
        'order' => 0,
        'is_active' => true,
    ]);

    $primary = Lesson::create([
        'section_id' => $section->id,
        'unit_id' => $unit->id,
        'title' => 'Unit lesson',
        'order' => 0,
        'duration' => 900,
        'is_active' => true,
    ]);

    $linked = Lesson::create([
        'section_id' => $section->id,
        'unit_id' => $unit->id,
        'title' => 'Linked lesson',
        'order' => 1,
        'duration' => 600,
        'is_active' => true,
    ]);

    $unit->linkedLessons()->attach($linked->id);

    $loaded = SubjectSection::with([
        'directLessons',
        'units.lessons',
        'units.linkedLessons',
        'mirroredUnits.lessons',
        'mirroredUnits.linkedLessons',
    ])->findOrFail($section->id);

    expect($loaded->totalLessonsDurationSecondsForDisplay())->toBe(1800);
    expect($primary->id)->not->toBeNull();
});

test('subject totalLessonsDurationSecondsForDisplay sums unique lessons across sections', function () {
    ['subject' => $subject, 'section' => $sectionA] = createDurationFixture();

    $sectionB = SubjectSection::create([
        'subject_id' => $subject->id,
        'title' => 'Section B',
        'order' => 1,
        'is_active' => true,
        'type' => SubjectSection::TYPE_LESSONS,
    ]);

    Lesson::create([
        'section_id' => $sectionA->id,
        'title' => 'A direct',
        'order' => 0,
        'duration' => 400,
        'is_active' => true,
    ]);

    Lesson::create([
        'section_id' => $sectionB->id,
        'title' => 'B direct',
        'order' => 0,
        'duration' => 500,
        'is_active' => true,
    ]);

    $loaded = Subject::with([
        'sections.directLessons',
        'sections.units.lessons',
        'sections.units.linkedLessons',
        'sections.mirroredUnits.lessons',
        'sections.mirroredUnits.linkedLessons',
    ])->findOrFail($subject->id);

    expect($loaded->totalLessonsDurationSecondsForDisplay())->toBe(900);
});
