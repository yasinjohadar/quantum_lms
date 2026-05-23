<?php

use App\Models\AIQuestionGeneration;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\User;
use App\Services\AI\AIQuestionGenerationService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
});

function advancedGenAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

function advancedGenCurriculum(): array
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
        'order' => 1,
        'is_active' => true,
    ]);

    $unit = Unit::create([
        'section_id' => $section->id,
        'title' => 'Unit '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    return compact('schoolClass', 'subject', 'unit');
}

function advancedGenPayload(array $overrides = []): array
{
    return array_merge([
        'source_type' => 'manual_text',
        'source_content' => 'نص تجريبي لتوليد الأسئلة',
        'question_types' => ['multiple_choice'],
        'number_of_questions' => 3,
        'difficulty_level' => 'medium',
    ], $overrides);
}

test('store advanced accepts optional subject and unit for manual text', function () {
    $admin = advancedGenAdmin();
    ['schoolClass' => $schoolClass, 'subject' => $subject, 'unit' => $unit] = advancedGenCurriculum();

    $generation = AIQuestionGeneration::create([
        'user_id' => $admin->id,
        'subject_id' => $subject->id,
        'unit_id' => $unit->id,
        'source_type' => 'manual_text',
        'source_content' => 'test',
        'question_type' => 'multiple_choice',
        'number_of_questions' => 3,
        'difficulty_level' => 'medium',
        'status' => 'pending',
    ]);

    $this->mock(AIQuestionGenerationService::class, function ($mock) use ($subject, $unit, $generation) {
        $mock->shouldReceive('generateFromText')
            ->once()
            ->withArgs(function (string $text, array $options) use ($subject, $unit) {
                expect($text)->toBe('نص تجريبي لتوليد الأسئلة');
                expect($options['subject_id'])->toBe($subject->id);
                expect($options['unit_id'])->toBe($unit->id);

                return true;
            })
            ->andReturn($generation);
    });

    $response = $this->actingAs($admin)
        ->post(route('admin.ai.question-generations.store-advanced'), advancedGenPayload([
            'class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'unit_id' => $unit->id,
        ]));

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
});

test('store advanced rejects unit without subject for manual text', function () {
    $admin = advancedGenAdmin();
    ['unit' => $unit] = advancedGenCurriculum();

    $response = $this->actingAs($admin)
        ->post(route('admin.ai.question-generations.store-advanced'), advancedGenPayload([
            'unit_id' => $unit->id,
        ]));

    $response->assertRedirect();
    $response->assertSessionHasErrors('unit_id');
});
