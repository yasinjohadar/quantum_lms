<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\LessonCompletion;
use App\Models\SubjectSection;
use App\Models\Unit;
use App\Services\VimeoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;
use App\Services\StaffNotificationService;
use App\Services\StudentContentNotificationService;

class LessonController extends Controller
{
    private function resolveAttachmentTitle(?string $inputTitle, ?\Illuminate\Http\UploadedFile $file, ?string $attachmentType): string
    {
        $title = trim((string) $inputTitle);
        if ($title !== '') {
            return $title;
        }

        if ($file) {
            $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $base = trim((string) $base);
            if ($base !== '') {
                return $base;
            }
        }

        if ($attachmentType === 'link') {
            return 'رابط مرفق';
        }

        return 'مرفق';
    }

    public function __construct()
    {
        $this->middleware(['permission:lesson-create'])->only('store');
        $this->middleware(['permission:lesson-edit'])->only(['update', 'reorder']);
        $this->middleware(['permission:lesson-delete'])->only('destroy');
        $this->middleware(['permission:lesson-show'])->only('show');
        $this->middleware(['permission:lesson-approve-review'])->only('approveReview');
        $this->middleware(['permission:lesson-reject-review'])->only('rejectReview');
    }

    /**
     * تخزين درس جديد تابع لوحدة معيّنة.
     */
    public function store(StoreLessonRequest $request, Unit $unit)
    {
        return $this->storeWithContext($request, $unit, null);
    }

    /**
     * تخزين درس جديد مرتبط مباشرة بقسم (بدون وحدة).
     */
    public function storeForSection(StoreLessonRequest $request, SubjectSection $section)
    {
        return $this->storeWithContext($request, null, $section);
    }

    private function storeWithContext(StoreLessonRequest $request, ?Unit $unit, ?SubjectSection $section)
    {
        $contextId = $unit?->id ?? $section?->id;
        Log::info('محاولة إنشاء درس جديد (context): ' . $contextId, $request->all());

        try {
            $hasAttachmentInput = $request->filled('attachment_title')
                || $request->filled('attachment_type')
                || $request->filled('attachment_url')
                || $request->filled('attachment_description')
                || $request->hasFile('attachment_file');

            if ($hasAttachmentInput) {
                $request->validate([
                    'attachment_title' => ['nullable', 'string', 'max:255'],
                    'attachment_type' => ['required', 'in:file,link,document,image,audio'],
                    'attachment_description' => ['nullable', 'string'],
                    'attachment_file' => ['nullable', 'file', 'max:51200'],
                    'attachment_url' => ['nullable', 'url', 'max:500'],
                ]);

                if ($request->input('attachment_type') === 'link' && !$request->filled('attachment_url')) {
                    throw ValidationException::withMessages([
                        'attachment_url' => 'رابط المرفق مطلوب عندما يكون نوع المرفق رابطاً.',
                    ]);
                }

                if ($request->input('attachment_type') !== 'link' && !$request->hasFile('attachment_file')) {
                    throw ValidationException::withMessages([
                        'attachment_file' => 'ملف المرفق مطلوب عندما يكون نوع المرفق ملفاً.',
                    ]);
                }
            }

            $subject = $unit?->section?->subject ?? $section?->subject;
            if (!$subject) {
                throw ValidationException::withMessages([
                    'section_id' => 'لا يمكن تحديد المادة المرتبطة بهذا الدرس.',
                ]);
            }

            $user = auth()->user();
            if ($user->usesTeacherAssignmentScope()) {
                if (!$user->isAssignedToSubject($subject->id) &&
                    !$user->isAssignedToClass($subject->class_id)) {
                    abort(403, 'غير مصرح لك بالوصول إلى هذه المادة');
                }
            }

            $data = $request->validated();
            $data['unit_id'] = $unit?->id;
            $data['section_id'] = $section?->id ?? $unit?->section_id;
            $data['is_free'] = $request->has('is_free');
            $data['is_preview'] = $request->has('is_preview');

            if ($data['unit_id'] && $data['section_id']) {
                $unitSectionId = Unit::where('id', $data['unit_id'])->value('section_id');
                if ((int) $unitSectionId !== (int) $data['section_id']) {
                    throw ValidationException::withMessages([
                        'section_id' => 'القسم المحدد لا يطابق الوحدة المحددة.',
                    ]);
                }
            }

            $isTeacher = $user->shouldSubmitContentForReview();
            if ($isTeacher) {
                if ($request->has('is_active')) {
                    $data['review_status'] = Lesson::REVIEW_STATUS_PENDING;
                    $data['submitted_for_review_at'] = now();
                    $data['is_active'] = false;
                } else {
                    $data['review_status'] = Lesson::REVIEW_STATUS_DRAFT;
                    $data['is_active'] = false;
                }
            } else {
                $data['is_active'] = $request->has('is_active');
                $data['review_status'] = $data['is_active']
                    ? Lesson::REVIEW_STATUS_APPROVED
                    : Lesson::REVIEW_STATUS_DRAFT;
            }

            if ($data['video_type'] === 'youtube' && !empty($data['video_url'])) {
                $data['video_id'] = Lesson::extractYoutubeId($data['video_url']);
            } elseif ($data['video_type'] === 'vimeo' && !empty($data['video_url'])) {
                $data['video_id'] = Lesson::extractVimeoId($data['video_url']);
                $duration = app(VimeoService::class)->getVideoDuration($data['video_url']);
                if ($duration !== null) {
                    $data['duration'] = (int) $duration;
                }
            }

            if ($request->hasFile('video_file')) {
                $videoFile = $request->file('video_file');
                $videoName = time() . '_' . $videoFile->getClientOriginalName();
                $data['video_url'] = $videoFile->storeAs('lessons/videos', $videoName, 'public');
            }

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $request->file('thumbnail');
                $thumbName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
                $data['thumbnail'] = $thumbnail->storeAs('lessons/thumbnails', $thumbName, 'public');
            }

            if (!isset($data['order']) || $data['order'] === null) {
                $query = Lesson::query();
                if (!empty($data['unit_id'])) {
                    $query->where('unit_id', $data['unit_id']);
                } else {
                    $query->whereNull('unit_id')->where('section_id', $data['section_id']);
                }
                $maxOrder = $query->max('order') ?? 0;
                $data['order'] = $maxOrder + 1;
            }

            $lesson = Lesson::create($data);

            if ($hasAttachmentInput) {
                $attachmentFile = $request->file('attachment_file');
                $attachmentType = $request->input('attachment_type');
                $resolvedTitle = $this->resolveAttachmentTitle(
                    $request->input('attachment_title'),
                    $attachmentFile,
                    $attachmentType
                );

                $attachmentData = [
                    'lesson_id' => $lesson->id,
                    'title' => $resolvedTitle,
                    'type' => $attachmentType,
                    'description' => $request->input('attachment_description'),
                    'is_downloadable' => $request->has('attachment_is_downloadable'),
                    'is_active' => true,
                    'order' => 1,
                ];

                if ($attachmentType === 'link') {
                    $attachmentData['url'] = $request->input('attachment_url');
                } elseif ($attachmentFile) {
                    $attachmentFileName = time() . '_attachment_' . $attachmentFile->getClientOriginalName();
                    $attachmentData['file_path'] = $attachmentFile->storeAs('lessons/attachments', $attachmentFileName, 'public');
                    $attachmentData['file_name'] = $attachmentFile->getClientOriginalName();
                    $attachmentData['file_type'] = $attachmentFile->getClientOriginalExtension();
                    $attachmentData['file_size'] = $attachmentFile->getSize();
                }

                LessonAttachment::create($attachmentData);
            }

            if ($lesson->review_status === Lesson::REVIEW_STATUS_PENDING && $user->shouldSubmitContentForReview()) {
                $this->dispatchNotificationSafely(function () use ($lesson, $user) {
                    app(StaffNotificationService::class)->notifyLessonSubmittedForReview($lesson->fresh(), $user);
                }, 'lesson_review_submitted', $lesson->id);
            }

            $this->dispatchNotificationSafely(function () use ($lesson, $user) {
                app(StudentContentNotificationService::class)->notifyIfLessonBecameVisible(null, $lesson->fresh(), $user);
            }, 'student_lesson_visible', $lesson->id);

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إنشاء الدرس "' . $lesson->title . '" بنجاح.',
                    'lesson_id' => $lesson->id,
                    'unit_id' => $lesson->unit_id,
                    'section_id' => $lesson->section_id,
                    'subject_id' => $subject->id,
                ]);
            }

            return redirect()
                ->route('admin.subjects.show', $subject->id)
                ->with('success', 'تم إنشاء الدرس "' . $lesson->title . '" بنجاح.');
        } catch (ValidationException $e) {
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعذر إنشاء الدرس بسبب أخطاء في البيانات المدخلة.',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء درس: ' . $e->getMessage());
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء إنشاء الدرس.',
                ], 500);
            }
            return redirect()->back()->with('error', 'حدث خطأ أثناء إنشاء الدرس: ' . $e->getMessage());
        }
    }

    /**
     * عرض صفحة الدرس (للتشغيل والمشاهدة).
     */
    public function show(Lesson $lesson)
    {
        $lesson->load(['unit.section.subject', 'section.subject', 'attachments']);
        
        // التحقق من التخصيص
        $user = auth()->user();
        $subject = $this->resolveSubjectFromLesson($lesson);
        if ($user->usesTeacherAssignmentScope()) {
            if (!$user->isAssignedToSubject($subject->id) && 
                !$user->isAssignedToClass($subject->class_id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذا الدرس');
            }
        }
        $enrolledUserIds = $subject->students()->wherePivot('status', 'active')->pluck('users.id')->toArray();
        $lessonCompletions = empty($enrolledUserIds)
            ? collect()
            : LessonCompletion::where('lesson_id', $lesson->id)
                ->whereIn('user_id', $enrolledUserIds)
                ->with('user')
                ->orderBy('updated_at', 'desc')
                ->get();
        
        return view('admin.pages.lessons.show', compact('lesson', 'lessonCompletions'));
    }

    /**
     * تحديث درس موجود.
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        try {
            // التحقق من التخصيص
            $user = auth()->user();
            $subject = $this->resolveSubjectFromLesson($lesson);
            if ($user->usesTeacherAssignmentScope()) {
                if (!$user->isAssignedToSubject($subject->id) && 
                    !$user->isAssignedToClass($subject->class_id)) {
                    abort(403, 'غير مصرح لك بالوصول إلى هذا الدرس');
                }
            }

            $lessonBeforeUpdate = clone $lesson;
            
            $data = $request->validated();
            $data['is_free'] = $request->has('is_free');
            $data['is_preview'] = $request->has('is_preview');

            // استبعاد linked_unit_ids من التحديث المباشر على الدرس
            $linkedUnitIds = $data['linked_unit_ids'] ?? [];
            unset($data['linked_unit_ids']);

            $oldReviewStatus = $lesson->review_status;
            $isTeacher = $user->shouldSubmitContentForReview();

            if ($isTeacher) {
                // إذا كان الدرس في حالة pending أو rejected وكان المعلم يحاول تفعيله
                if ($request->has('is_active') && in_array($lesson->review_status, [Lesson::REVIEW_STATUS_PENDING, Lesson::REVIEW_STATUS_REJECTED])) {
                    $data['review_status'] = Lesson::REVIEW_STATUS_PENDING;
                    $data['submitted_for_review_at'] = now();
                    $data['review_notes'] = null; // مسح الملاحظات القديمة
                    $data['is_active'] = false;
                } elseif ($request->has('is_active')) {
                    // إذا كان draft ويحاول تفعيله
                    $data['review_status'] = Lesson::REVIEW_STATUS_PENDING;
                    $data['submitted_for_review_at'] = now();
                    $data['is_active'] = false;
                } else {
                    // إذا لم يحاول تفعيله، يبقى draft
                    $data['review_status'] = Lesson::REVIEW_STATUS_DRAFT;
                    $data['is_active'] = false;
                }
            } else {
                // المشرف والمدير
                $data['is_active'] = $request->has('is_active');
                if ($data['is_active'] && $lesson->review_status !== Lesson::REVIEW_STATUS_APPROVED) {
                    $data['review_status'] = Lesson::REVIEW_STATUS_APPROVED;
                } elseif (!$data['is_active']) {
                    $data['review_status'] = Lesson::REVIEW_STATUS_DRAFT;
                }
            }

            // معالجة نوع الفيديو واستخراج المعرف
            if ($data['video_type'] === 'youtube' && !empty($data['video_url'])) {
                $data['video_id'] = Lesson::extractYoutubeId($data['video_url']);
            } elseif ($data['video_type'] === 'vimeo' && !empty($data['video_url'])) {
                $data['video_id'] = Lesson::extractVimeoId($data['video_url']);
                $duration = app(VimeoService::class)->getVideoDuration($data['video_url']);
                if ($duration !== null) {
                    $data['duration'] = (int) $duration;
                }
            }

            // رفع ملف الفيديو الجديد
            if ($request->hasFile('video_file')) {
                // حذف الفيديو القديم
                if ($lesson->video_url && $lesson->video_type === 'upload') {
                    StorageHelper::delete('videos', $lesson->video_url);
                }

                $videoFile = $request->file('video_file');
                $videoName = time() . '_' . $videoFile->getClientOriginalName();
                $data['video_url'] = $videoFile->storeAs('lessons/videos', $videoName, 'public');
            }

            // رفع الصورة المصغرة الجديدة
            if ($request->hasFile('thumbnail')) {
                // حذف الصورة القديمة
                if ($lesson->thumbnail) {
                    StorageHelper::delete('images', $lesson->thumbnail);
                }

                $thumbnail = $request->file('thumbnail');
                $thumbName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
                $data['thumbnail'] = $thumbnail->storeAs('lessons/thumbnails', $thumbName, 'public');
            }

            $lesson->update($data);
            $lesson->refresh();

            if (
                $lesson->review_status === Lesson::REVIEW_STATUS_PENDING
                && $oldReviewStatus !== Lesson::REVIEW_STATUS_PENDING
                && $user->shouldSubmitContentForReview()
            ) {
                $this->dispatchNotificationSafely(function () use ($lesson, $user) {
                    app(StaffNotificationService::class)->notifyLessonSubmittedForReview($lesson->fresh(), $user);
                }, 'lesson_review_submitted', $lesson->id);
            }

            // مزامنة الوحدات الإضافية (ربط الدرس بوحدات أخرى): استبعاد الوحدة الأصلية
            $linkedUnitIds = array_values(array_unique(array_filter($linkedUnitIds)));
            $primaryUnitId = $lesson->unit_id;
            $linkedUnitIds = array_values(array_diff($linkedUnitIds, [$primaryUnitId]));

            // للمعلم: السماح فقط بوحدات من مواد/صفوف مخصصة له
            if ($user->usesTeacherAssignmentScope()) {
                $classIds = $user->assignedClasses()->pluck('classes.id');
                $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
                $allowedUnitIds = \App\Models\Unit::whereHas('section.subject', function ($q) use ($classIds, $subjectIds) {
                    if ($classIds->isNotEmpty() || $subjectIds->isNotEmpty()) {
                        $q->where(function ($sq) use ($classIds, $subjectIds) {
                            if ($classIds->isNotEmpty()) {
                                $sq->whereIn('class_id', $classIds);
                            }
                            if ($subjectIds->isNotEmpty()) {
                                $sq->orWhereIn('id', $subjectIds);
                            }
                        });
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })->pluck('id')->toArray();
                $linkedUnitIds = array_values(array_intersect($linkedUnitIds, $allowedUnitIds));
            }

            $lesson->linkedUnits()->sync($linkedUnitIds);

            $subjectId = $subject->id;

            $this->dispatchNotificationSafely(function () use ($lessonBeforeUpdate, $lesson, $user) {
                app(StudentContentNotificationService::class)->notifyIfLessonBecameVisible(
                    $lessonBeforeUpdate,
                    $lesson->fresh(),
                    $user
                );
            }, 'student_lesson_visible', $lesson->id);

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث الدرس بنجاح.',
                    'lesson_id' => $lesson->id,
                    'unit_id' => $lesson->unit_id,
                    'section_id' => $lesson->section_id,
                    'subject_id' => $subjectId,
                ]);
            }

            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('success', 'تم تحديث الدرس بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في تحديث درس: ' . $e->getMessage());

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تحديث الدرس.',
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث الدرس: ' . $e->getMessage());
        }
    }

    /**
     * حذف درس.
     */
    public function destroy(Request $request, Lesson $lesson)
    {
        // التحقق من التخصيص
        $user = auth()->user();
        $subject = $this->resolveSubjectFromLesson($lesson);
        if ($user->usesTeacherAssignmentScope()) {
            if (!$user->isAssignedToSubject($subject->id) && 
                !$user->isAssignedToClass($subject->class_id)) {
                if ($this->wantsJsonResponse($request)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'غير مصرح لك بالوصول إلى هذا الدرس.',
                    ], 403);
                }

                abort(403, 'غير مصرح لك بالوصول إلى هذا الدرس');
            }
        }
        
        $subjectId = $subject->id;
        $lessonTitle = $lesson->title;

        try {
            // حذف ملفات الدرس
            if ($lesson->video_url && $lesson->video_type === 'upload') {
                StorageHelper::delete('videos', $lesson->video_url);
            }
            if ($lesson->thumbnail) {
                StorageHelper::delete('images', $lesson->thumbnail);
            }

            // حذف مرفقات الدرس
            foreach ($lesson->attachments as $attachment) {
                if ($attachment->file_path) {
                    StorageHelper::delete('attachments', $attachment->file_path);
                }
            }

            $lesson->delete();

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف الدرس "' . $lessonTitle . '" بنجاح.',
                    'lesson_id' => $lesson->id,
                    'unit_id' => $lesson->unit_id,
                    'section_id' => $lesson->section_id,
                    'subject_id' => $subjectId,
                ]);
            }

            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('success', 'تم حذف الدرس "' . $lessonTitle . '" بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في حذف درس: ' . $e->getMessage());

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حذف الدرس.',
                ], 500);
            }

            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('error', 'حدث خطأ أثناء حذف الدرس: ' . $e->getMessage());
        }
    }

    /**
     * إعادة ترتيب الدروس ضمن الوحدة.
     */
    public function reorder(Request $request, Unit $unit)
    {
        // التحقق من التخصيص
        $user = auth()->user();
        if ($user->usesTeacherAssignmentScope()) {
            $subject = $unit->section->subject;
            if (!$user->isAssignedToSubject($subject->id) &&
                !$user->isAssignedToClass($subject->class_id)) {
                return response()->json(['success' => false, 'message' => 'غير مصرح لك بالوصول إلى هذه الوحدة.'], 403);
            }
        }

        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:lessons,id'],
        ]);

        $order = $request->input('order');
        $unitLessonIds = Lesson::where('unit_id', $unit->id)->whereIn('id', $order)->pluck('id')->toArray();

        $index = 0;
        foreach ($order as $lessonId) {
            if (in_array((int) $lessonId, $unitLessonIds, true)) {
                Lesson::where('id', $lessonId)->update(['order' => $index]);
                $index++;
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * الموافقة على تفعيل الدرس
     */
    public function approveReview(Request $request, Lesson $lesson)
    {
        $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $lessonBeforeApprove = clone $lesson;

        $lesson->update([
            'review_status' => Lesson::REVIEW_STATUS_APPROVED,
            'is_active' => true,
            'review_notes' => $request->input('review_notes'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->dispatchNotificationSafely(function () use ($lesson) {
            app(StaffNotificationService::class)->notifyLessonReviewOutcome($lesson->fresh(), auth()->user(), true);
        }, 'lesson_review_approved', $lesson->id);

        app(StudentContentNotificationService::class)->notifyIfLessonBecameVisible(
            $lessonBeforeApprove,
            $lesson->fresh(),
            auth()->user()
        );

        $subjectId = $this->resolveSubjectFromLesson($lesson)->id;

        return redirect()
            ->route('admin.subjects.show', $subjectId)
            ->with('success', 'تم الموافقة على تفعيل الدرس بنجاح.');
    }

    /**
     * رفض تفعيل الدرس مع ملاحظات
     */
    public function rejectReview(Request $request, Lesson $lesson)
    {
        $request->validate([
            'review_notes' => 'required|string|max:1000',
        ]);

        $lesson->update([
            'review_status' => Lesson::REVIEW_STATUS_REJECTED,
            'is_active' => false,
            'review_notes' => $request->input('review_notes'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->dispatchNotificationSafely(function () use ($lesson) {
            app(StaffNotificationService::class)->notifyLessonReviewOutcome($lesson->fresh(), auth()->user(), false);
        }, 'lesson_review_rejected', $lesson->id);

        $subjectId = $this->resolveSubjectFromLesson($lesson)->id;

        return redirect()
            ->route('admin.subjects.show', $subjectId)
            ->with('success', 'تم رفض تفعيل الدرس وتم إرسال الملاحظات للمعلم.');
    }

    private function resolveSubjectFromLesson(Lesson $lesson)
    {
        if ($lesson->relationLoaded('unit') && $lesson->unit && $lesson->unit->relationLoaded('section') && $lesson->unit->section) {
            return $lesson->unit->section->subject;
        }

        if ($lesson->unit && $lesson->unit->section) {
            return $lesson->unit->section->subject;
        }

        if ($lesson->relationLoaded('section') && $lesson->section) {
            return $lesson->section->subject;
        }

        return optional($lesson->section)->subject;
    }

    private function wantsJsonResponse(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    private function dispatchNotificationSafely(callable $callback, string $context, int $lessonId): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('Lesson notification dispatch failed: ' . $e->getMessage(), [
                'context' => $context,
                'lesson_id' => $lessonId,
            ]);
        }
    }
}

