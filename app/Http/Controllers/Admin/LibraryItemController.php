<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryItem;
use App\Models\LibraryCategory;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\LibraryTag;
use App\Services\LibraryService;
use App\Services\LibraryStatsService;
use App\Events\LibraryItemCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;
use Illuminate\Support\Facades\Event;

class LibraryItemController extends Controller
{
    public function __construct(
        private LibraryService $libraryService,
        private LibraryStatsService $statsService
    ) {}

    /**
     * عرض قائمة العناصر
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LibraryItem::with(['category', 'subject', 'uploader']);

        // صلاحيات المعلم: يرى فقط العناصر التي رفعها أو العناصر المرتبطة بالمواد التي يدرّسها
        if ($user->hasRole('teacher') && !$user->hasRole('admin')) {
            $teacherSubjects = $user->subjects()->pluck('subjects.id')->toArray();
            $query->where(function($q) use ($user, $teacherSubjects) {
                // العناصر التي رفعها المعلم
                $q->where('uploaded_by', $user->id)
                  // أو العناصر المرتبطة بالمواد التي يدرّسها
                  ->orWhereIn('subject_id', $teacherSubjects);
            });
        }
        // الأدمن يرى الكل (لا فلترة)

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // فلترة حسب التصنيف
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // فلترة حسب المادة
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        // فلترة حسب الحالة
        if ($request->filled('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        // الترتيب
        $orderBy = $request->input('order_by', 'created_at');
        $orderDir = $request->input('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $items = $query->paginate(20);
        $categories = LibraryCategory::active()->ordered()->get();
        
        // للمعلم: عرض المواد التي يدرّسها فقط
        if ($user->hasRole('teacher') && !$user->hasRole('admin')) {
            $subjects = $user->subjects()->active()->ordered()->get();
        } else {
            $subjects = Subject::active()->ordered()->get();
        }

        return view('admin.pages.library.items.index', compact('items', 'categories', 'subjects'));
    }

    /**
     * عرض نموذج إنشاء عنصر جديد
     */
    public function create()
    {
        $categories = LibraryCategory::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        $subjects = Subject::active()->ordered()->get();
        $tags = LibraryTag::all();

        return view('admin.pages.library.items.create', compact('categories', 'classes', 'subjects', 'tags'));
    }

    /**
     * حفظ عنصر جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:library_items,slug',
            'description' => 'nullable|string',
            'type' => 'required|in:file,link,video,document,book,worksheet',
            'category_id' => 'required|exists:library_categories,id',
            'class_id' => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'file' => 'nullable|file|max:51200', // 50MB max
            'external_url' => 'nullable|url|max:500',
            'is_featured' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'access_level' => 'required|in:public,enrolled,restricted',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:library_tags,id',
        ], [
            'title.required' => 'العنوان مطلوب',
            'type.required' => 'نوع العنصر مطلوب',
            'category_id.required' => 'التصنيف مطلوب',
            'file.max' => 'حجم الملف يجب ألا يتجاوز 50 ميجابايت',
            'external_url.url' => 'الرابط يجب أن يكون صالحاً',
        ]);

        try {
            // إذا كان النوع ملف، يجب رفع ملف
            if ($validated['type'] !== 'link' && !$request->hasFile('file') && !$request->filled('external_url')) {
                return redirect()->back()
                               ->with('error', 'يجب رفع ملف أو إدخال رابط خارجي.')
                               ->withInput();
            }

            // إذا كان النوع رابط، يجب إدخال رابط
            if ($validated['type'] === 'link' && !$request->filled('external_url')) {
                return redirect()->back()
                               ->with('error', 'يجب إدخال رابط خارجي للعناصر من نوع رابط.')
                               ->withInput();
            }

            $validated['is_featured'] = $request->has('is_featured');
            $validated['is_public'] = $request->has('is_public') || $request->filled('subject_id');

            $item = $this->libraryService->createItem($validated, Auth::user());

            // رفع الملف إذا كان موجوداً
            if ($request->hasFile('file')) {
                $this->libraryService->uploadFile($item, $request->file('file'));
            }

            // إرسال إشعار للطلاب إذا كان العنصر مرتبط بمادة
            if ($item->subject_id) {
                Event::dispatch(new LibraryItemCreated($item));
            }

            return redirect()->route('admin.library.items.index')
                           ->with('success', 'تم إنشاء العنصر بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating library item: ' . $e->getMessage(), ['request' => $validated]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء العنصر: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * عرض عنصر معين
     */
    public function show(LibraryItem $item)
    {
        $user = Auth::user();
        
        // صلاحيات المعلم: التحقق من إمكانية الوصول
        if ($user->hasRole('teacher') && !$user->hasRole('admin')) {
            $teacherSubjects = $user->subjects()->pluck('subjects.id')->toArray();
            $canAccess = $item->uploaded_by === $user->id || in_array($item->subject_id, $teacherSubjects);
            
            if (!$canAccess) {
                abort(403, 'ليس لديك صلاحية للوصول إلى هذا العنصر.');
            }
        }
        
        $item->load(['category', 'subject', 'uploader', 'tags', 'downloads.user', 'views.user', 'ratings.user']);
        $stats = $this->statsService->getItemStats($item);

        return view('admin.pages.library.items.show', compact('item', 'stats'));
    }

    /**
     * عرض نموذج تعديل عنصر
     */
    public function edit(LibraryItem $item)
    {
        $user = Auth::user();
        
        // صلاحيات المعلم: يمكنه تعديل العناصر التي رفعها فقط
        if ($user->hasRole('teacher') && !$user->hasRole('admin')) {
            if ($item->uploaded_by !== $user->id) {
                abort(403, 'يمكنك تعديل العناصر التي رفعتها فقط.');
            }
        }
        
        $item->load('tags');
        $categories = LibraryCategory::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        
        // إذا كان العنصر مرتبط بصف، احمّل المواد المرتبطة بهذا الصف فقط
        // وإلا احمّل جميع المواد (للتوافق مع البيانات القديمة)
        if ($user->hasRole('teacher') && !$user->hasRole('admin')) {
            $query = $user->subjects()->active()->ordered();
            if ($item->class_id) {
                $query->where('class_id', $item->class_id);
            }
            $subjects = $query->get();
        } else {
            $query = Subject::active()->ordered();
            if ($item->class_id) {
                $query->where('class_id', $item->class_id);
            }
            $subjects = $query->get();
        }
        
        $tags = LibraryTag::all();

        return view('admin.pages.library.items.edit', compact('item', 'categories', 'classes', 'subjects', 'tags'));
    }

    /**
     * تحديث عنصر
     */
    public function update(Request $request, LibraryItem $item)
    {
        $user = Auth::user();
        
        // صلاحيات المعلم: يمكنه تحديث العناصر التي رفعها فقط
        if ($user->hasRole('teacher') && !$user->hasRole('admin')) {
            if ($item->uploaded_by !== $user->id) {
                abort(403, 'يمكنك تحديث العناصر التي رفعتها فقط.');
            }
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:library_items,slug,' . $item->id,
            'description' => 'nullable|string',
            'type' => 'required|in:file,link,video,document,book,worksheet',
            'category_id' => 'required|exists:library_categories,id',
            'class_id' => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'file' => 'nullable|file|max:51200',
            'external_url' => 'nullable|url|max:500',
            'is_featured' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'access_level' => 'required|in:public,enrolled,restricted',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:library_tags,id',
        ], [
            'title.required' => 'العنوان مطلوب',
            'type.required' => 'نوع العنصر مطلوب',
            'category_id.required' => 'التصنيف مطلوب',
            'file.max' => 'حجم الملف يجب ألا يتجاوز 50 ميجابايت',
            'external_url.url' => 'الرابط يجب أن يكون صالحاً',
        ]);

        try {
            $validated['is_featured'] = $request->has('is_featured');
            $validated['is_public'] = $request->has('is_public') || $request->filled('subject_id');

            // رفع ملف جديد إذا كان موجوداً
            if ($request->hasFile('file')) {
                // حذف الملف القديم
                if ($item->file_path) {
                    StorageHelper::delete('library', $item->file_path);
                }
                $this->libraryService->uploadFile($item, $request->file('file'));
            }

            $this->libraryService->updateItem($item, $validated);

            return redirect()->route('admin.library.items.index')
                           ->with('success', 'تم تحديث العنصر بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating library item: ' . $e->getMessage(), ['item_id' => $item->id, 'request' => $validated]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تحديث العنصر: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * حذف عنصر
     */
    public function destroy(LibraryItem $item)
    {
        $user = Auth::user();
        
        // صلاحيات المعلم: يمكنه حذف العناصر التي رفعها فقط
        if ($user->hasRole('teacher') && !$user->hasRole('admin')) {
            if ($item->uploaded_by !== $user->id) {
                abort(403, 'يمكنك حذف العناصر التي رفعتها فقط.');
            }
        }
        
        try {
            $this->libraryService->deleteItem($item);

            return redirect()->route('admin.library.items.index')
                           ->with('success', 'تم حذف العنصر بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting library item: ' . $e->getMessage(), ['item_id' => $item->id]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف العنصر.');
        }
    }

    /**
     * معاينة عنصر
     */
    public function preview(LibraryItem $item)
    {
        if (!$item->file_path && !$item->external_url) {
            abort(404, 'لا يوجد ملف أو رابط للمعاينة.');
        }

        return view('admin.pages.library.items.preview', compact('item'));
    }

    /**
     * تحميل ملف عنصر
     */
    public function download(LibraryItem $item)
    {
        if (!$item->file_path) {
            abort(404, 'لا يوجد ملف للتحميل.');
        }

        $disk = StorageHelper::disk('library');
        if ($disk->exists($item->file_path)) {
            return $disk->download($item->file_path, $item->file_name);
        }

        abort(404, 'الملف غير موجود.');
    }

    /**
     * عرض إحصائيات عنصر
     */
    public function stats(LibraryItem $item)
    {
        $stats = $this->statsService->getItemStats($item);

        return view('admin.pages.library.items.stats', compact('item', 'stats'));
    }

    /**
     * جلب المواد المرتبطة بصف معين
     */
    public function getSubjectsByClass(Request $request)
    {
        try {
            $request->validate([
                'class_id' => 'required|exists:classes,id',
            ]);

            $subjects = Subject::where('class_id', $request->class_id)
                ->active()
                ->ordered()
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'subjects' => $subjects ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching subjects by class: ' . $e->getMessage(), [
                'class_id' => $request->class_id,
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المواد',
                'subjects' => [],
            ], 500);
        }
    }
}
