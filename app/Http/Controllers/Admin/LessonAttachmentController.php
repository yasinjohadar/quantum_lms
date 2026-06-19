<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Services\LessonAttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Helpers\StorageHelper;
use App\Services\Storage\MediaStorageService;

class LessonAttachmentController extends Controller
{
    public function __construct(
        private LessonAttachmentService $attachmentService
    ) {
        $this->middleware(['permission:lesson-attachment-create'])->only('store');
        $this->middleware(['permission:lesson-attachment-edit'])->only('update');
        $this->middleware(['permission:lesson-attachment-delete'])->only('destroy');
    }

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

    /**
     * تخزين مرفق جديد للدرس.
     */
    public function store(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'type' => 'nullable|in:file,link,document,image,audio',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:51200',
            'files' => 'nullable|array|max:20',
            'files.*' => 'file|max:51200',
            'url' => 'nullable|url|max:500',
        ], [
            'type.in' => 'نوع المرفق غير صالح',
            'file.file' => 'ملف المرفق غير صالح',
            'file.max' => 'حجم الملف يجب ألا يتجاوز 50 ميجابايت',
            'files.max' => 'يمكن رفع 20 ملفاً كحد أقصى في المرة الواحدة',
            'files.*.file' => 'أحد الملفات المرفوعة غير صالح',
            'files.*.max' => 'حجم كل ملف يجب ألا يتجاوز 50 ميجابايت',
            'url.url' => 'الرابط يجب أن يكون صالحاً',
            'url.max' => 'الرابط يجب ألا يتجاوز 500 حرف',
        ]);

        $type = $request->input('type');
        $uploadedFiles = array_filter($request->file('files') ?? []);
        $singleFile = $request->file('file');

        if ($type === 'link') {
            if (!$request->filled('url')) {
                throw ValidationException::withMessages([
                    'url' => 'رابط المرفق مطلوب عندما يكون نوع المرفق رابطًا.',
                ]);
            }
        } elseif ($uploadedFiles === [] && !$singleFile) {
            throw ValidationException::withMessages([
                'files' => 'يجب اختيار ملف واحد على الأقل.',
            ]);
        }

        try {
            $createdCount = 0;
            $commonOptions = [
                'description' => $request->description,
                'is_downloadable' => $request->has('is_downloadable'),
                'is_active' => true,
            ];

            if ($type === 'link') {
                $this->attachmentService->createFromLink($lesson, array_merge($commonOptions, [
                    'title' => $request->input('title'),
                    'url' => $request->url,
                ]));
                $createdCount = 1;
            } elseif ($uploadedFiles !== []) {
                foreach ($uploadedFiles as $file) {
                    $this->attachmentService->createFromUploadedFile($lesson, $file, array_merge($commonOptions, [
                        'title' => count($uploadedFiles) === 1 ? $request->input('title') : null,
                    ]));
                    $createdCount++;
                }
            } else {
                $this->attachmentService->createFromUploadedFile($lesson, $singleFile, array_merge($commonOptions, [
                    'title' => $request->input('title'),
                    'type' => $type ?: $this->attachmentService->detectType($singleFile),
                ]));
                $createdCount = 1;
            }

            $returnUrl = $this->resolveReturnUrl($request->input('return_to'), $lesson->id);
            $message = $createdCount === 1
                ? 'تم إضافة المرفق بنجاح.'
                : "تم إضافة {$createdCount} مرفقات بنجاح.";

            return redirect()
                ->to($returnUrl)
                ->with('success', $message);
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
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:51200',
            'url' => 'nullable|url|max:500',
        ]);

        try {
            $uploadedFile = $request->file('file');
            $resolvedTitle = $this->attachmentService->resolveTitle(
                $request->input('title'),
                $uploadedFile,
                $attachment->type === 'link',
                $attachment->file_name
            );

            $data = [
                'title' => $resolvedTitle,
                'description' => $request->description,
                'is_downloadable' => $request->has('is_downloadable'),
            ];

            if ($uploadedFile) {
                if ($attachment->file_path) {
                    MediaStorageService::delete($attachment->file_path);
                }

                $uploadResult = $this->attachmentService->uploadAttachmentFile(
                    $uploadedFile,
                    $this->attachmentService->detectType($uploadedFile)
                );
                $data['file_path'] = $uploadResult['path'];
                $data['file_name'] = $uploadedFile->getClientOriginalName();
                $data['file_type'] = $uploadedFile->getClientOriginalExtension();
                $data['file_size'] = $uploadedFile->getSize();
            }

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
