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

function attachmentBulkAdmin(): User
{
    $adminRole = Role::firstOrCreate(
        ['name' => 'admin', 'guard_name' => 'web'],
        ['dashboard_type' => 'admin', 'staff_profile' => 'none']
    );

    foreach (['lesson-attachment-create'] as $permissionName) {
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        if (! $adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }
    }

    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole($adminRole);

    return $admin;
}

function createAttachmentBulkFixture(): array
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

    $lesson = Lesson::create([
        'unit_id' => $unit->id,
        'section_id' => $section->id,
        'title' => 'Lesson '.$suffix,
        'order' => 1,
        'is_active' => true,
    ]);

    return compact('lesson');
}

function mockAttachmentUploadService(): void
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

test('detectType maps extensions to attachment types', function () {
    $service = new LessonAttachmentService();

    expect($service->detectType(UploadedFile::fake()->create('notes.pdf', 10)))->toBe('document')
        ->and($service->detectType(UploadedFile::fake()->image('photo.png')))->toBe('image')
        ->and($service->detectType(UploadedFile::fake()->create('track.mp3', 10)))->toBe('audio')
        ->and($service->detectType(UploadedFile::fake()->create('archive.zip', 10)))->toBe('file');
});

test('bulk upload creates multiple attachments with auto-detected types', function () {
    ['lesson' => $lesson] = createAttachmentBulkFixture();
    mockAttachmentUploadService();

    $admin = attachmentBulkAdmin();

    $response = $this->actingAs($admin)->post(route('admin.lessons.attachments.store', $lesson), [
        'files' => [
            UploadedFile::fake()->create('notes.pdf', 100),
            UploadedFile::fake()->image('photo.png'),
            UploadedFile::fake()->create('track.mp3', 100),
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $attachments = LessonAttachment::where('lesson_id', $lesson->id)->orderBy('order')->get();

    expect($attachments)->toHaveCount(3)
        ->and($attachments[0]->type)->toBe('document')
        ->and($attachments[1]->type)->toBe('image')
        ->and($attachments[2]->type)->toBe('audio');
});

test('legacy single file upload with explicit type still works', function () {
    ['lesson' => $lesson] = createAttachmentBulkFixture();
    mockAttachmentUploadService();

    $admin = attachmentBulkAdmin();

    $response = $this->actingAs($admin)->post(route('admin.lessons.attachments.store', $lesson), [
        'type' => 'image',
        'file' => UploadedFile::fake()->image('legacy.jpg'),
        'title' => 'Legacy Image',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'تم إضافة المرفق بنجاح.');

    $attachment = LessonAttachment::where('lesson_id', $lesson->id)->sole();

    expect($attachment->type)->toBe('image')
        ->and($attachment->title)->toBe('Legacy Image');
});

test('bulk upload preserves existing attachments', function () {
    ['lesson' => $lesson] = createAttachmentBulkFixture();

    LessonAttachment::create([
        'lesson_id' => $lesson->id,
        'type' => 'file',
        'title' => 'Existing attachment',
        'file_path' => 'lessons/attachments/existing.pdf',
        'order' => 1,
        'is_active' => true,
    ]);

    mockAttachmentUploadService();

    $admin = attachmentBulkAdmin();

    $this->actingAs($admin)->post(route('admin.lessons.attachments.store', $lesson), [
        'files' => [
            UploadedFile::fake()->create('new.pdf', 100),
        ],
    ])->assertRedirect();

    $attachments = LessonAttachment::where('lesson_id', $lesson->id)->orderBy('order')->get();

    expect($attachments)->toHaveCount(2)
        ->and($attachments->first()->title)->toBe('Existing attachment')
        ->and($attachments->last()->type)->toBe('document');
});
