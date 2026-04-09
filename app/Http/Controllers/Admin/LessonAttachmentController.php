<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Helpers\StorageHelper;

class LessonAttachmentController extends Controller
{
    private function resolveReturnUrl(?string $returnTo, int $lessonId): string
    {
        $fallback = route('admin.lessons.show', $lessonId);
        if (!$returnTo) {
            return $fallback;
        }

        if (str_starts_with($returnTo, '/')) {
            return $returnTo;
        }

        if (filter_var($returnTo, FILTER_VALIDATE_URL)) {
            $host = parse_url($returnTo, PHP_URL_HOST);
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            if ($host && $appHost && $host === $appHost) {
                return $returnTo;
            }
        }

        return $fallback;
    }

    public function __construct()
    {
        $this->middleware(['permission:lesson-attachment-create'])->only('store');
        $this->middleware(['permission:lesson-attachment-edit'])->only('update');
        $this->middleware(['permission:lesson-attachment-delete'])->only('destroy');
    }

    /**
     * تخزين مرفق جديد للدرس.
     */
    public function store(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:file,link,document,image,audio',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:51200', // 50MB max
            'url' => 'nullable|url|max:500',
        ], [
            'title.required' => 'عنوان المرفق مطلوب',
            'type.required' => 'نوع المرفق مطلوب',
            'type.in' => 'نوع المرفق غير صالح',
            'file.file' => 'ملف المرفق غير صالح',
            'file.max' => 'حجم الملف يجب ألا يتجاوز 50 ميجابايت',
            'url.required' => 'رابط المرفق مطلوب عندما يكون نوع المرفق رابطًا',
            'url.url' => 'الرابط يجب أن يكون صالحاً',
            'url.max' => 'الرابط يجب ألا يتجاوز 500 حرف',
        ]);

        if ($request->input('type') === 'link' && !$request->filled('url')) {
            throw ValidationException::withMessages([
                'url' => 'رابط المرفق مطلوب عندما يكون نوع المرفق رابطًا.',
            ]);
        }

        if ($request->input('type') !== 'link' && !$request->hasFile('file')) {
            throw ValidationException::withMessages([
                'file' => 'ملف المرفق مطلوب عندما يكون نوع المرفق ملفًا.',
            ]);
        }

        try {
            $data = [
                'lesson_id' => $lesson->id,
                'title' => $request->title,
                'type' => $request->type,
                'description' => $request->description,
                'is_downloadable' => $request->has('is_downloadable'),
                'is_active' => $request->has('is_active') || true,
            ];

            // رفع الملف
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $data['file_path'] = $file->storeAs('lessons/attachments', $fileName, 'public');
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getClientOriginalExtension();
                $data['file_size'] = $file->getSize();
            }

            // الرابط الخارجي
            if ($request->type === 'link' && $request->url) {
                $data['url'] = $request->url;
            }

            // الترتيب
            $maxOrder = $lesson->attachments()->max('order') ?? 0;
            $data['order'] = $maxOrder + 1;

            LessonAttachment::create($data);

            $returnUrl = $this->resolveReturnUrl($request->input('return_to'), $lesson->id);

            return redirect()
                ->to($returnUrl)
                ->with('success', 'تم إضافة المرفق بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في إضافة مرفق: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إضافة المرفق: ' . $e->getMessage());
        }
    }

    /**
     * تحديث مرفق.
     */
    public function update(Request $request, LessonAttachment $attachment)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:51200',
            'url' => 'nullable|url|max:500',
        ]);

        try {
            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'is_downloadable' => $request->has('is_downloadable'),
            ];

            // تحديث الملف إذا تم رفع ملف جديد
            if ($request->hasFile('file')) {
                // حذف الملف القديم
                if ($attachment->file_path) {
                    StorageHelper::delete('attachments', $attachment->file_path);
                }

                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $data['file_path'] = $file->storeAs('lessons/attachments', $fileName, 'public');
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getClientOriginalExtension();
                $data['file_size'] = $file->getSize();
            }

            // تحديث الرابط
            if ($attachment->type === 'link' && $request->url) {
                $data['url'] = $request->url;
            }

            $attachment->update($data);

            $returnUrl = $this->resolveReturnUrl($request->input('return_to'), $attachment->lesson_id);

            return redirect()
                ->to($returnUrl)
                ->with('success', 'تم تحديث المرفق بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في تحديث مرفق: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث المرفق: ' . $e->getMessage());
        }
    }

    /**
     * حذف مرفق.
     */
    public function destroy(LessonAttachment $attachment)
    {
        $returnUrl = $this->resolveReturnUrl(request()->input('return_to'), $attachment->lesson_id);

        try {
            // حذف الملف
            if ($attachment->file_path) {
                StorageHelper::delete('attachments', $attachment->file_path);
            }

            $attachment->delete();

            return redirect()
                ->to($returnUrl)
                ->with('success', 'تم حذف المرفق بنجاح.');
        } catch (\Exception $e) {
            Log::error('خطأ في حذف مرفق: ' . $e->getMessage());

            return redirect()
                ->to($returnUrl)
                ->with('error', 'حدث خطأ أثناء حذف المرفق: ' . $e->getMessage());
        }
    }
}

