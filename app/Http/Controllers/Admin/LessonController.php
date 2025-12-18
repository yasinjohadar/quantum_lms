<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    /**
     * تخزين درس جديد تابع لوحدة معيّنة.
     */
    public function store(StoreLessonRequest $request, Unit $unit)
    {
        Log::info('محاولة إنشاء درس جديد للوحدة: ' . $unit->id, $request->all());

        try {
            $data = $request->validated();
            $data['unit_id'] = $unit->id;
            $data['is_active'] = $request->has('is_active');
            $data['is_free'] = $request->has('is_free');
            $data['is_preview'] = $request->has('is_preview');

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
        return view('admin.pages.lessons.show', compact('lesson'));
    }

    /**
     * تحديث درس موجود.
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');
            $data['is_free'] = $request->has('is_free');
            $data['is_preview'] = $request->has('is_preview');

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
                    Storage::disk('public')->delete($lesson->video_url);
                }

                $videoFile = $request->file('video_file');
                $videoName = time() . '_' . $videoFile->getClientOriginalName();
                $data['video_url'] = $videoFile->storeAs('lessons/videos', $videoName, 'public');
            }

            // رفع الصورة المصغرة الجديدة
            if ($request->hasFile('thumbnail')) {
                // حذف الصورة القديمة
                if ($lesson->thumbnail) {
                    Storage::disk('public')->delete($lesson->thumbnail);
                }

                $thumbnail = $request->file('thumbnail');
                $thumbName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
                $data['thumbnail'] = $thumbnail->storeAs('lessons/thumbnails', $thumbName, 'public');
            }

            $lesson->update($data);

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
        $subjectId = $lesson->unit->section->subject_id;
        $lessonTitle = $lesson->title;

        try {
            // حذف ملفات الدرس
            if ($lesson->video_url && $lesson->video_type === 'upload') {
                Storage::disk('public')->delete($lesson->video_url);
            }
            if ($lesson->thumbnail) {
                Storage::disk('public')->delete($lesson->thumbnail);
            }

            // حذف مرفقات الدرس
            foreach ($lesson->attachments as $attachment) {
                if ($attachment->file_path) {
                    Storage::disk('public')->delete($attachment->file_path);
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
}

