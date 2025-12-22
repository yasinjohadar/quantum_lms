<?php

namespace App\Services;

use App\Models\LibraryItem;
use App\Models\LibraryCategory;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LibraryService
{
    /**
     * إنشاء عنصر جديد في المكتبة
     */
    public function createItem(array $data, User $uploader): LibraryItem
    {
        // توليد slug إذا لم يكن موجوداً
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $data['uploaded_by'] = $uploader->id;

        $item = LibraryItem::create($data);

        // ربط الوسوم إذا كانت موجودة
        if (isset($data['tags']) && is_array($data['tags'])) {
            $item->tags()->sync($data['tags']);
        }

        Log::info('Library item created successfully', ['item_id' => $item->id, 'uploader_id' => $uploader->id]);

        return $item;
    }

    /**
     * تحديث عنصر في المكتبة
     */
    public function updateItem(LibraryItem $item, array $data): LibraryItem
    {
        // تحديث slug إذا تغير العنوان
        if (isset($data['title']) && $data['title'] !== $item->title) {
            $data['slug'] = Str::slug($data['title']);
        }

        $item->update($data);

        // تحديث الوسوم
        if (isset($data['tags']) && is_array($data['tags'])) {
            $item->tags()->sync($data['tags']);
        }

        Log::info('Library item updated successfully', ['item_id' => $item->id]);

        return $item->fresh();
    }

    /**
     * حذف عنصر من المكتبة
     */
    public function deleteItem(LibraryItem $item): bool
    {
        // حذف الملف من التخزين
        if ($item->file_path) {
            Storage::disk('public')->delete($item->file_path);
        }

        // حذف الصورة المصغرة
        if ($item->thumbnail) {
            Storage::disk('public')->delete($item->thumbnail);
        }

        $item->delete();

        Log::info('Library item deleted successfully', ['item_id' => $item->id]);

        return true;
    }

    /**
     * رفع ملف للعنصر
     */
    public function uploadFile(LibraryItem $item, UploadedFile $file, ?string $directory = null): array
    {
        $directory = $directory ?? "library/items/{$item->id}";
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs($directory, $fileName, 'public');

        $data = [
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
        ];

        // تحديث العنصر
        $item->update($data);

        // توليد thumbnail تلقائياً للصور
        if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $this->generateThumbnail($item);
        }

        Log::info('File uploaded for library item', ['item_id' => $item->id, 'file_path' => $filePath]);

        return $data;
    }

    /**
     * معالجة الملف (توليد thumbnail إذا لزم الأمر)
     */
    public function processFile(LibraryItem $item): void
    {
        // توليد thumbnail تلقائياً للصور
        if ($item->file_path && in_array(strtolower($item->file_type), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $this->generateThumbnail($item);
        }
    }

    /**
     * توليد صورة مصغرة
     */
    public function generateThumbnail(LibraryItem $item): ?string
    {
        if (!$item->file_path || !Storage::disk('public')->exists($item->file_path)) {
            return null;
        }

        $fileExtension = strtolower($item->file_type);
        
        // فقط للصور
        if (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return null;
        }

        try {
            // استخدام Intervention Image إذا كان متوفراً
            if (class_exists(\Intervention\Image\Facades\Image::class)) {
                $image = \Intervention\Image\Facades\Image::make(Storage::disk('public')->path($item->file_path));
                $image->fit(400, 300, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $thumbnailPath = "library/thumbnails/{$item->id}/" . time() . '.jpg';
                $image->save(Storage::disk('public')->path($thumbnailPath), 80);

                $item->update(['thumbnail' => $thumbnailPath]);
                return $thumbnailPath;
            } else {
                // إذا لم يكن Intervention Image متوفراً، نسخ الصورة الأصلية كـ thumbnail
                // (يمكن استبدالها لاحقاً بمكتبة أخرى)
                $thumbnailPath = "library/thumbnails/{$item->id}/" . time() . '.' . $fileExtension;
                Storage::disk('public')->copy($item->file_path, $thumbnailPath);
                $item->update(['thumbnail' => $thumbnailPath]);
                return $thumbnailPath;
            }
        } catch (\Exception $e) {
            Log::error('Error generating thumbnail: ' . $e->getMessage(), ['item_id' => $item->id]);
            return null;
        }
    }

    /**
     * التحقق من إمكانية وصول المستخدم للعنصر
     */
    public function canUserAccess(LibraryItem $item, ?User $user): bool
    {
        return $item->canUserDownload($user);
    }

    /**
     * التحقق من إمكانية تحميل المستخدم للعنصر
     */
    public function canUserDownload(LibraryItem $item, ?User $user): bool
    {
        return $item->canUserDownload($user);
    }

    /**
     * الحصول على العناصر العامة
     */
    public function getPublicItems(array $filters = [])
    {
        $query = LibraryItem::with(['category', 'subject', 'uploader', 'tags'])
                           ->public()
                           ->where('access_level', 'public');

        // فلترة حسب التصنيف
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // فلترة حسب النوع
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // فلترة حسب المادة
        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        // البحث
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // الترتيب
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * الحصول على عناصر مادة معينة
     */
    public function getSubjectItems(Subject $subject, ?User $user = null, array $filters = [])
    {
        $query = LibraryItem::with(['category', 'uploader', 'tags'])
                           ->where('subject_id', $subject->id);

        // التحقق من التسجيل إذا كان المستخدم موجوداً
        if ($user) {
            $isEnrolled = $subject->students()->where('users.id', $user->id)->exists();
            
            // إذا لم يكن مسجل، عرض العناصر العامة فقط
            if (!$isEnrolled) {
                $query->where('access_level', 'public');
            }
        } else {
            // للزوار غير المسجلين، عرض العناصر العامة فقط
            $query->where('access_level', 'public');
        }

        // تطبيق الفلاتر
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * البحث في العناصر
     */
    public function searchItems(string $query, array $filters = [])
    {
        $searchQuery = LibraryItem::with(['category', 'subject', 'uploader', 'tags'])
                                 ->where(function($q) use ($query) {
                                     $q->where('title', 'like', "%{$query}%")
                                       ->orWhere('description', 'like', "%{$query}%");
                                 });

        // فلترة حسب التصنيف
        if (isset($filters['category_id'])) {
            $searchQuery->where('category_id', $filters['category_id']);
        }

        // فلترة حسب النوع
        if (isset($filters['type'])) {
            $searchQuery->where('type', $filters['type']);
        }

        // فلترة حسب المادة
        if (isset($filters['subject_id'])) {
            $searchQuery->where('subject_id', $filters['subject_id']);
        }

        // فلترة حسب التقييم
        if (isset($filters['min_rating'])) {
            $searchQuery->where('average_rating', '>=', $filters['min_rating']);
        }

        // الترتيب
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $searchQuery->orderBy($orderBy, $orderDir);

        return $searchQuery->paginate($filters['per_page'] ?? 20);
    }
}

