<?php

use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Models\User;
use App\Services\LessonAttachmentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migrations are incompatible; run with MySQL.');
    }
    if (! Schema::hasTable('lesson_attachments') || ! Schema::hasTable('lessons')) {
        $this->markTestSkipped('Database schema not migrated; run migrations on MySQL.');
    }
});

function lessonCreateAttachmentsAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    foreach (['lesson-create'] as $permissionName) {
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        if (! $adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }
    }

    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole($adminRole);

    return $admin;
}

function createLessonCreateAttachmentsFixture(): array
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
    ]);

    $unit = Unit::create([
        'section_id' => $section->id,
        'title' => 'Unit '.$suffix,
        'order' => 0,
        'is_active' => true,
    ]);

    return compact('section', 'unit');
}

function lessonCreateBasePayload(): array
{
    return [
        'title' => 'درس اختبار المرفقات',
        'video_type' => 'youtube',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'description' => 'وصف الدرس',
        'is_active' => '1',
    ];
}

function mockLessonCreateAttachmentService(): void
{
    test()->mock(LessonAttachmentService::class, function ($mock) {
        $mock->shouldReceive('detectType')
            ->andReturnUsing(fn (UploadedFile $file) => (new LessonAttachmentService())->detectType($file));

        $mock->shouldReceive('createFromUploadedFile')
            ->andReturnUsing(function (Lesson $lesson, UploadedFile $file, array $options = []) {
                $service = new LessonAttachmentService();

                return LessonAttachment::create([
                    'lesson_id' => $lesson->id,
                    'title' => $service->resolveTitle($options['title'] ?? null, $file),
                    'type' => $options['type'] ?? $service->detectType($file),
                    'description' => $options['description'] ?? null,
                    'file_path' => 'lessons/attachments/'.$file->getClientOriginalName(),
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'order' => $options['order'] ?? ((int) $lesson->attachments()->max('order')) + 1,
                    'is_downloadable' => $options['is_downloadable'] ?? true,
                    'is_active' => $options['is_active'] ?? true,
                ]);
            });

        $mock->shouldReceive('createFromLink')
            ->andReturnUsing(function (Lesson $lesson, array $options = []) {
                $service = new LessonAttachmentService();

                return LessonAttachment::create([
                    'lesson_id' => $lesson->id,
                    'title' => $service->resolveTitle($options['title'] ?? null, null, true),
                    'type' => 'link',
                    'description' => $options['description'] ?? null,
                    'url' => $options['url'],
                    'order' => $options['order'] ?? ((int) $lesson->attachments()->max('order')) + 1,
                    'is_downloadable' => $options['is_downloadable'] ?? true,
                    'is_active' => $options['is_active'] ?? true,
                ]);
            });
    });
}

test('creating a lesson with multiple attachment files stores each attachment with detected types', function () {
    mockLessonCreateAttachmentService();
    $admin = lessonCreateAttachmentsAdmin();
    ['unit' => $unit] = createLessonCreateAttachmentsFixture();

    $response = $this->actingAs($admin)->post(route('admin.units.lessons.store', $unit), array_merge(lessonCreateBasePayload(), [
        'attachment_files' => [
            UploadedFile::fake()->create('notes.pdf', 10),
            UploadedFile::fake()->image('photo.png'),
            UploadedFile::fake()->create('track.mp3', 10),
        ],
        'attachment_is_downloadable' => '1',
    ]));

    $response->assertRedirect();
    $lesson = Lesson::where('title', 'درس اختبار المرفقات')->first();
    expect($lesson)->not->toBeNull();

    $attachments = LessonAttachment::where('lesson_id', $lesson->id)->orderBy('id')->get();
    expect($attachments)->toHaveCount(3)
        ->and($attachments->pluck('type')->all())->toBe(['document', 'image', 'audio']);
});

test('creating a lesson with attachment url only stores one link attachment', function () {
    mockLessonCreateAttachmentService();
    $admin = lessonCreateAttachmentsAdmin();
    ['section' => $section] = createLessonCreateAttachmentsFixture();

    $response = $this->actingAs($admin)->post(route('admin.sections.lessons.store', $section), array_merge(lessonCreateBasePayload(), [
        'attachment_url' => 'https://example.com/resource',
        'attachment_title' => 'رابط خارجي',
    ]));

    $response->assertRedirect();
    $lesson = Lesson::where('title', 'درس اختبار المرفقات')->whereNull('unit_id')->first();
    expect($lesson)->not->toBeNull();

    $attachments = LessonAttachment::where('lesson_id', $lesson->id)->get();
    expect($attachments)->toHaveCount(1)
        ->and($attachments->first()->type)->toBe('link')
        ->and($attachments->first()->url)->toBe('https://example.com/resource')
        ->and($attachments->first()->title)->toBe('رابط خارجي');
});

test('creating a lesson without attachments stores no attachment rows', function () {
    mockLessonCreateAttachmentService();
    $admin = lessonCreateAttachmentsAdmin();
    ['unit' => $unit] = createLessonCreateAttachmentsFixture();

    $response = $this->actingAs($admin)->post(route('admin.units.lessons.store', $unit), lessonCreateBasePayload());

    $response->assertRedirect();
    $lesson = Lesson::where('title', 'درس اختبار المرفقات')->first();
    expect($lesson)->not->toBeNull()
        ->and(LessonAttachment::where('lesson_id', $lesson->id)->count())->toBe(0);
});
