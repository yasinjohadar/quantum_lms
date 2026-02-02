<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;

class LessonController extends Controller
{
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
        Log::info('محاولة إنشاء درس جديد للوحدة: ' . $unit->id, $request->all());

        try {
            // التحقق من التخصيص
            $user = auth()->user();
            if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
                $subject = $unit->section->subject;
                if (!$user->isAssignedToSubject($subject->id) && 
                    !$user->isAssignedToClass($subject->class_id)) {
                    abort(403, 'غير مصرح لك بالوصول إلى هذه المادة');
                }
            }
            
            $data = $request->validated();
            $data['unit_id'] = $unit->id;
            $data['is_free'] = $request->has('is_free');
            $data['is_preview'] = $request->has('is_preview');

            // منطق المراجعة: إذا كان المستخدم معلم وليس مشرف أو مدير
            $isTeacher = $user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor']);

            if ($isTeacher) {
                // إذا حاول تفعيل الدرس، ضعه في حالة قيد المراجعة
                if ($request->has('is_active')) {
                    $data['review_status'] = Lesson::REVIEW_STATUS_PENDING;
                    $data['submitted_for_review_at'] = now();
                    $data['is_active'] = false; // لا يتم تفعيله مباشرة
                } else {
                    $data['review_status'] = Lesson::REVIEW_STATUS_DRAFT;
                    $data['is_active'] = false;
                }
            } else {
                // المشرف والمدير يمكنهم التفعيل مباشرة
                $data['is_active'] = $request->has('is_active');
                if ($data['is_active']) {
                    $data['review_status'] = Lesson::REVIEW_STATUS_APPROVED;
                } else {
                    $data['review_status'] = Lesson::REVIEW_STATUS_DRAFT;
                }
            }

            // معالجة نوع الفيديو واستخراج المعرف
            if ($data['video_type'] === 'youtube' && !empty($data['video_url'])) {
                $data['video_id'] = Lesson::extractYoutubeId($data['video_url']);
            } elseif ($data['video_type'] === 'vimeo' && !empty($data['video_url'])) {
                $data['video_id'] = Lesson::extractVimeoId($data['video_url']);
            }

            // رفع ملف الفيديو
            if ($request->hasFile('video_file')) {
                $videoFile = $request->file('video_file');
                $videoName = time() . '_' . $videoFile->getClientOriginalName();
                $data['video_url'] = $videoFile->storeAs('lessons/videos', $videoName, 'public');
            }

            // رفع الصورة المصغرة
            if ($request->hasFile('thumbnail')) {
                $thumbnail = $request->file('thumbnail');
                $thumbName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
                $data['thumbnail'] = $thumbnail->storeAs('lessons/thumbnails', $thumbName, 'public');
            }

            // تحديد الترتيب تلقائياً
            if (!isset($data['order']) || $data['order'] === null) {
                $maxOrder = $unit->lessons()->max('order') ?? 0;
                $data['order'] = $maxOrder + 1;
            }

            $lesson = Lesson::create($data);

            Log::info('تم إنشاء الدرس بنجاح، ID: ' . $lesson->id);

            // الحصول على subject_id للتوجيه
            $subjectId = $unit->section->subject_id;

            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('success', 'تم إنشاء الدرس "' . $lesson->title . '" بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء درس: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إنشاء الدرس: ' . $e->getMessage());
        }
    }

    /**
     * عرض صفحة الدرس (للتشغيل والمشاهدة).
     */
    public function show(Lesson $lesson)
    {
        $lesson->load(['unit.section.subject', 'attachments']);
        
        // التحقق من التخصيص
        $user = auth()->user();
        if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            $subject = $lesson->unit->section->subject;
            if (!$user->isAssignedToSubject($subject->id) && 
                !$user->isAssignedToClass($subject->class_id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذا الدرس');
            }
        }
        
        return view('admin.pages.lessons.show', compact('lesson'));
    }

    /**
     * تحديث درس موجود.
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        try {
            // التحقق من التخصيص
            $user = auth()->user();
            if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
                $subject = $lesson->unit->section->subject;
                if (!$user->isAssignedToSubject($subject->id) && 
                    !$user->isAssignedToClass($subject->class_id)) {
                    abort(403, 'غير مصرح لك بالوصول إلى هذا الدرس');
                }
            }
            
            $data = $request->validated();
            $data['is_free'] = $request->has('is_free');
            $data['is_preview'] = $request->has('is_preview');

            // استبعاد linked_unit_ids من التحديث المباشر على الدرس
            $linkedUnitIds = $data['linked_unit_ids'] ?? [];
            unset($data['linked_unit_ids']);

            $isTeacher = $user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor']);

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

            // مزامنة الوحدات الإضافية (ربط الدرس بوحدات أخرى): استبعاد الوحدة الأصلية
            $linkedUnitIds = array_values(array_unique(array_filter($linkedUnitIds)));
            $primaryUnitId = $lesson->unit_id;
            $linkedUnitIds = array_values(array_diff($linkedUnitIds, [$primaryUnitId]));

            // للمعلم: السماح فقط بوحدات من مواد/صفوف مخصصة له
            if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
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

            $subjectId = $lesson->unit->section->subject_id;

            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('success', 'تم تحديث الدرس بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في تحديث درس: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث الدرس: ' . $e->getMessage());
        }
    }

    /**
     * حذف درس.
     */
    public function destroy(Lesson $lesson)
    {
        // التحقق من التخصيص
        $user = auth()->user();
        if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            $subject = $lesson->unit->section->subject;
            if (!$user->isAssignedToSubject($subject->id) && 
                !$user->isAssignedToClass($subject->class_id)) {
                abort(403, 'غير مصرح لك بالوصول إلى هذا الدرس');
            }
        }
        
        $subjectId = $lesson->unit->section->subject_id;
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

            return redirect()
                ->route('admin.subjects.show', $subjectId)
                ->with('success', 'تم حذف الدرس "' . $lessonTitle . '" بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في حذف درس: ' . $e->getMessage());

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
        if ($user->hasRole('teacher') && !$user->hasAnyRole(['admin', 'supervisor'])) {
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

        $lesson->update([
            'review_status' => Lesson::REVIEW_STATUS_APPROVED,
            'is_active' => true,
            'review_notes' => $request->input('review_notes'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $subjectId = $lesson->unit->section->subject_id;

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

        $subjectId = $lesson->unit->section->subject_id;

        return redirect()
            ->route('admin.subjects.show', $subjectId)
            ->with('success', 'تم رفض تفعيل الدرس وتم إرسال الملاحظات للمعلم.');
    }
}

