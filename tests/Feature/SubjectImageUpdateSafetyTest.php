<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Stage;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SubjectImageUpdateSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected Stage $stage;

    protected Currency $currency;

    protected SchoolClass $class;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped('Admin permission/role stack and image validation require MySQL migrations.');
        }

        Storage::fake('public');

        $this->stage = Stage::create(['name' => 'Image Stage', 'order' => 1]);
        $this->currency = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_active' => true,
            'order' => 1,
        ]);
        $this->class = SchoolClass::create([
            'name' => 'Image Class',
            'slug' => 'image-class-'.uniqid(),
            'stage_id' => $this->stage->id,
            'is_active' => true,
            'is_free' => true,
            'price' => 0,
            'default_currency_id' => $this->currency->id,
        ]);

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['dashboard_type' => 'admin', 'staff_profile' => 'none']
        );
        Permission::firstOrCreate(['name' => 'subject-create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'subject-edit', 'guard_name' => 'web']);

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole($adminRole);
        $this->admin->givePermissionTo(['subject-create', 'subject-edit']);
    }

    protected function baseSubjectPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Subject '.uniqid(),
            'class_id' => $this->class->id,
            'is_active' => '1',
            'display_in_class' => '1',
            'order' => 0,
            'price' => 0,
            'is_free' => '1',
            'pricing_mode' => 'inherit',
            'default_currency_id' => $this->currency->id,
        ], $overrides);
    }

    // A: رفع صورة جديدة لمادة بلا صورة سابقة
    public function test_store_uploads_image_for_subject_with_no_prior_image(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.subjects.store'), $this->baseSubjectPayload([
            'name' => 'Fresh Subject',
            'image' => UploadedFile::fake()->image('cover.jpg', 200, 200),
        ]));

        $response->assertRedirect();

        $subject = Subject::where('class_id', $this->class->id)->where('name', 'Fresh Subject')->firstOrFail();

        $this->assertNotNull($subject->image);
        Storage::disk('public')->assertExists($subject->image);
    }

    // B: تعديل مادة لديها صورة، ورفع صورة جديدة — الجديدة تعمل، DB يحمل المسار الجديد، والقديمة تُحذف
    public function test_update_replaces_image_and_removes_old_one_after_success(): void
    {
        $createResponse = $this->actingAs($this->admin)->post(route('admin.subjects.store'), $this->baseSubjectPayload([
            'name' => 'Subject With Image',
            'image' => UploadedFile::fake()->image('old.jpg', 200, 200),
        ]));
        $createResponse->assertRedirect();

        $subject = Subject::where('class_id', $this->class->id)->where('name', 'Subject With Image')->firstOrFail();
        $oldImagePath = $subject->image;
        $this->assertNotNull($oldImagePath);
        Storage::disk('public')->assertExists($oldImagePath);

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.subjects.update', $subject->id), $this->baseSubjectPayload([
            'name' => $subject->name,
            'image' => UploadedFile::fake()->image('new.jpg', 200, 200),
        ]));
        $updateResponse->assertRedirect();

        $subject->refresh();
        $this->assertNotNull($subject->image);
        $this->assertNotEquals($oldImagePath, $subject->image);
        Storage::disk('public')->assertExists($subject->image);
        Storage::disk('public')->assertMissing($oldImagePath);
    }

    // C: محاكاة فشل رفع الصورة الجديدة — القديمة تبقى سليمة تماماً، subjects.image لا يتغيّر
    public function test_update_keeps_old_image_intact_when_new_upload_fails(): void
    {
        $createResponse = $this->actingAs($this->admin)->post(route('admin.subjects.store'), $this->baseSubjectPayload([
            'name' => 'Subject Upload Failure',
            'image' => UploadedFile::fake()->image('old.jpg', 200, 200),
        ]));
        $createResponse->assertRedirect();

        $subject = Subject::where('class_id', $this->class->id)->where('name', 'Subject Upload Failure')->firstOrFail();
        $oldImagePath = $subject->image;
        $this->assertNotNull($oldImagePath);
        Storage::disk('public')->assertExists($oldImagePath);

        // نجبر فشل رفع الصورة الجديدة عبر توجيه قرص public الفعلي (غير المزيّف) لجذر غير قابل
        // للكتابة، ولا يوجد تخزين سحابي مفعّل في بيئة الاختبار، فيفشل upload() بالكامل ويرمي
        // استثناءً قبل الوصول لأي حذف أو تحديث في قاعدة البيانات.
        $originalRoot = config('filesystems.disks.public.root');
        config(['filesystems.disks.public.root' => 'Z:/definitely-not-writable-'.uniqid()]);
        Storage::forgetDisk('public');

        try {
            $updateResponse = $this->actingAs($this->admin)->put(route('admin.subjects.update', $subject->id), $this->baseSubjectPayload([
                'name' => $subject->name,
                'image' => UploadedFile::fake()->image('new.jpg', 200, 200),
            ]));

            $updateResponse->assertRedirect();
            $updateResponse->assertSessionHas('error');

            $subject->refresh();
            $this->assertSame($oldImagePath, $subject->image);
        } finally {
            config(['filesystems.disks.public.root' => $originalRoot]);
            Storage::forgetDisk('public');
        }
    }

    // D (تغطية جزئية): إن كانت الصورة القديمة قد اختفت فعلياً من التخزين قبل التعديل (مثلاً حُذفت
    // يدوياً سابقاً)، فإن محاولة حذفها مجدداً بعد نجاح التحديث يجب ألا تكسر الاستجابة — تحديث
    // المادة ينجح والصورة الجديدة تعمل رغم أن حذف "القديمة" كان no-op بلا أي ملف فعلي.
    public function test_update_succeeds_even_if_old_image_file_was_already_missing(): void
    {
        $createResponse = $this->actingAs($this->admin)->post(route('admin.subjects.store'), $this->baseSubjectPayload([
            'name' => 'Subject Already Missing Old Image',
            'image' => UploadedFile::fake()->image('old.jpg', 200, 200),
        ]));
        $createResponse->assertRedirect();

        $subject = Subject::where('class_id', $this->class->id)->where('name', 'Subject Already Missing Old Image')->firstOrFail();
        $oldImagePath = $subject->image;
        $this->assertNotNull($oldImagePath);

        // نحذف الملف الفعلي يدوياً مسبقاً، لمحاكاة حالة "الصورة القديمة مفقودة أصلاً من التخزين"
        Storage::disk('public')->delete($oldImagePath);
        Storage::disk('public')->assertMissing($oldImagePath);

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.subjects.update', $subject->id), $this->baseSubjectPayload([
            'name' => $subject->name,
            'image' => UploadedFile::fake()->image('new.jpg', 200, 200),
        ]));

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $subject->refresh();
        $this->assertNotNull($subject->image);
        $this->assertNotEquals($oldImagePath, $subject->image);
        Storage::disk('public')->assertExists($subject->image);
    }

    // E: رفع صورتين متتاليتين بنفس اسم الملف الأصلي — يجب ألا تتشاركا نفس المسار الفعلي
    public function test_two_uploads_with_identical_original_filename_do_not_collide(): void
    {
        $responseOne = $this->actingAs($this->admin)->post(route('admin.subjects.store'), $this->baseSubjectPayload([
            'name' => 'Collision Subject One',
            'image' => UploadedFile::fake()->image('photo.jpg', 50, 50)->size(30),
        ]));
        $responseOne->assertRedirect();

        $responseTwo = $this->actingAs($this->admin)->post(route('admin.subjects.store'), $this->baseSubjectPayload([
            'name' => 'Collision Subject Two',
            'image' => UploadedFile::fake()->image('photo.jpg', 60, 60)->size(40),
        ]));
        $responseTwo->assertRedirect();

        $subjectOne = Subject::where('name', 'Collision Subject One')->firstOrFail();
        $subjectTwo = Subject::where('name', 'Collision Subject Two')->firstOrFail();

        $this->assertNotSame($subjectOne->image, $subjectTwo->image);
        Storage::disk('public')->assertExists($subjectOne->image);
        Storage::disk('public')->assertExists($subjectTwo->image);
    }

    // F: التأكد أن مادة أخرى سليمة لم تتأثر إطلاقاً بتعديل مادة أخرى
    public function test_editing_one_subject_does_not_affect_another_subjects_image(): void
    {
        $untouchedResponse = $this->actingAs($this->admin)->post(route('admin.subjects.store'), $this->baseSubjectPayload([
            'name' => 'Untouched Subject',
            'image' => UploadedFile::fake()->image('untouched.jpg', 200, 200),
        ]));
        $untouchedResponse->assertRedirect();
        $untouched = Subject::where('name', 'Untouched Subject')->firstOrFail();
        $untouchedImagePath = $untouched->image;

        $editedResponse = $this->actingAs($this->admin)->post(route('admin.subjects.store'), $this->baseSubjectPayload([
            'name' => 'Edited Subject',
            'image' => UploadedFile::fake()->image('to-edit.jpg', 200, 200),
        ]));
        $editedResponse->assertRedirect();
        $edited = Subject::where('name', 'Edited Subject')->firstOrFail();

        $this->actingAs($this->admin)->put(route('admin.subjects.update', $edited->id), $this->baseSubjectPayload([
            'name' => $edited->name,
            'image' => UploadedFile::fake()->image('replacement.jpg', 200, 200),
        ]))->assertRedirect();

        $untouched->refresh();
        $this->assertSame($untouchedImagePath, $untouched->image);
        Storage::disk('public')->assertExists($untouchedImagePath);
    }
}
