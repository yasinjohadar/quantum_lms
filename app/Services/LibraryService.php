<?php

namespace App\Services;

use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use App\Services\Storage\MediaStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LibraryService
{
    public function createItem(array $data, User $uploader): LibraryItem
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        $data['uploaded_by'] = $uploader->id;

        return LibraryItem::create($data);
    }

    public function updateItem(LibraryItem $item, array $data): LibraryItem
    {
        if (empty($data['slug']) && ($data['title'] ?? $item->title) !== $item->title) {
            $data['slug'] = Str::slug($data['title']);
        }
        $item->update($data);

        return $item;
    }

    public function deleteItem(LibraryItem $item): void
    {
        if ($item->file_path) {
            MediaStorageService::delete($item->file_path);
        }
        $item->delete();
    }

    /**
     * رفع ملف العنصر كملف خاص — لا يُخدَّم إلا عبر مسار التحميل المحكوم بالصلاحيات.
     */
    public function uploadFile(LibraryItem $item, UploadedFile $file): LibraryItem
    {
        $directory = "library/items/{$item->id}";
        $fileName = time().'_'.Str::random(10).'.'.$file->getClientOriginalExtension();

        $uploadResult = MediaStorageService::uploadPrivateFile($file, $directory, $fileName);

        $item->update([
            'file_path' => $uploadResult['path'],
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
        ]);

        return $item;
    }

    public function canUserAccess(LibraryItem $item, ?User $user): bool
    {
        return $item->canUserDownload($user);
    }

    public function canUserDownload(LibraryItem $item, ?User $user): bool
    {
        return $item->canUserDownload($user);
    }

    /**
     * استجابة تحميل الملف — يُحلّ القرص الفعلي من سجل MediaFile (السحابة أو المحلي)
     * ثم يُبَث عبر Storage::disk()->download()، فيعمل بنفس الطريقة بصرف النظر عن مزوّد التخزين.
     */
    public function downloadResponse(LibraryItem $item): StreamedResponse
    {
        $disk = MediaFile::where('path', $item->file_path)->value('disk')
            ?? config('storage.fallback_disk', 'public');

        return Storage::disk($disk)->download($item->file_path, $item->file_name);
    }

    /**
     * عناصر المكتبة المرئية لطالب معيّن: عناصر عامة غير مرتبطة بمادة/صف،
     * أو مرتبطة بمادة/صف الطالب المسجَّل فيه.
     */
    public function getStudentItems(User $user, array $filters = [])
    {
        $subjectIds = $user->enrollments()->active()->pluck('subject_id');
        $classIds = $user->classEnrollments()->approved()->pluck('class_id');

        $query = LibraryItem::query()
            ->with(['category', 'subject', 'schoolClass'])
            ->where('is_public', true)
            ->where('access_level', 'public')
            ->where(function ($q) use ($subjectIds, $classIds) {
                $q->where(function ($q2) {
                    $q2->whereNull('subject_id')->whereNull('class_id');
                });
                if ($subjectIds->isNotEmpty()) {
                    $q->orWhereIn('subject_id', $subjectIds);
                }
                if ($classIds->isNotEmpty()) {
                    $q->orWhereIn('class_id', $classIds);
                }
            });

        if (! empty($filters['category_id'])) {
            $query->byCategory((int) $filters['category_id']);
        }
        if (! empty($filters['type'])) {
            $query->byType($filters['type']);
        }
        if (! empty($filters['subject_id'])) {
            $query->forSubject((int) $filters['subject_id']);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
    }
}
