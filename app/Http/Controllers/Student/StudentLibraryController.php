<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LibraryItem;
use App\Models\LibraryCategory;
use App\Models\Subject;
use App\Models\LibraryFavorite;
use App\Services\LibraryService;
use App\Services\LibraryRatingService;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StudentLibraryController extends Controller
{
    public function __construct(
        private LibraryService $libraryService,
        private LibraryRatingService $ratingService,
        private GamificationService $gamificationService
    ) {}

    /**
     * عرض قائمة المكتبة
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = [
            'category_id' => $request->input('category_id'),
            'type' => $request->input('type'),
            'subject_id' => $request->input('subject_id'),
            'search' => $request->input('search'),
            'order_by' => $request->input('order_by', 'created_at'),
            'order_dir' => $request->input('order_dir', 'desc'),
            'per_page' => 20,
        ];

        $items = $this->libraryService->getPublicItems($filters);
        $categories = LibraryCategory::active()->ordered()->get();
        $subjects = $user->subjects()->active()->get();

        return view('student.pages.library.index', compact('items', 'categories', 'subjects'));
    }

    /**
     * عرض عنصر معين
     */
    public function show(LibraryItem $item)
    {
        $user = Auth::user();

        // التحقق من إمكانية الوصول
        if (!$this->libraryService->canUserAccess($item, $user)) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذا العنصر.');
        }

        // تسجيل المشاهدة
        $item->incrementView($user);

        // منح نقاط للمشاهدة (إذا كان مفعلاً)
        $this->gamificationService->processEvent($user, 'library_item_viewed', [
            'item_id' => $item->id,
            'item_title' => $item->title,
        ]);

        $item->load(['category', 'subject', 'uploader', 'tags', 'ratings.user']);
        $userRating = $item->ratings()->where('user_id', $user->id)->first();

        return view('student.pages.library.show', compact('item', 'userRating'));
    }

    /**
     * معاينة عنصر
     */
    public function preview(LibraryItem $item)
    {
        $user = Auth::user();

        // التحقق من إمكانية الوصول
        if (!$this->libraryService->canUserAccess($item, $user)) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذا العنصر.');
        }

        // تسجيل المشاهدة
        $item->incrementView($user);

        return view('student.pages.library.preview', compact('item'));
    }

    /**
     * تحميل عنصر
     */
    public function download(LibraryItem $item)
    {
        $user = Auth::user();

        // التحقق من إمكانية التحميل
        if (!$this->libraryService->canUserDownload($item, $user)) {
            abort(403, 'ليس لديك صلاحية لتحميل هذا العنصر.');
        }

        // إذا كان رابط خارجي، إعادة التوجيه
        if ($item->external_url) {
            return redirect($item->external_url);
        }

        // إذا كان ملف
        if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
            // تسجيل التحميل
            $item->incrementDownload($user);

            // منح نقاط للتحميل (إذا كان مفعلاً)
            $this->gamificationService->processEvent($user, 'library_item_downloaded', [
                'item_id' => $item->id,
                'item_title' => $item->title,
            ]);

            return Storage::disk('public')->download($item->file_path, $item->file_name);
        }

        abort(404, 'الملف غير موجود.');
    }

    /**
     * تقييم عنصر
     */
    public function rate(Request $request, LibraryItem $item)
    {
        $user = Auth::user();

        // التحقق من إمكانية الوصول
        if (!$this->libraryService->canUserAccess($item, $user)) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذا العنصر.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'التقييم مطلوب',
            'rating.min' => 'التقييم يجب أن يكون بين 1 و 5',
            'rating.max' => 'التقييم يجب أن يكون بين 1 و 5',
        ]);

        try {
            $this->ratingService->rateItem($item, $user, $validated['rating'], $validated['comment'] ?? null);

            return redirect()->back()
                           ->with('success', 'تم تقييم العنصر بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error rating library item: ' . $e->getMessage(), ['item_id' => $item->id, 'user_id' => $user->id]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تقييم العنصر.');
        }
    }

    /**
     * البحث في المكتبة
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $query = $request->input('q', '');

        if (empty($query)) {
            return redirect()->route('student.library.index');
        }

        $filters = [
            'category_id' => $request->input('category_id'),
            'type' => $request->input('type'),
            'subject_id' => $request->input('subject_id'),
            'min_rating' => $request->input('min_rating'),
            'order_by' => $request->input('order_by', 'created_at'),
            'order_dir' => $request->input('order_dir', 'desc'),
            'per_page' => 20,
        ];

        $items = $this->libraryService->searchItems($query, $filters);
        $categories = LibraryCategory::active()->ordered()->get();
        $subjects = $user->subjects()->active()->get();

        return view('student.pages.library.search', compact('items', 'categories', 'subjects', 'query'));
    }

    /**
     * عرض مكتبة مادة معينة
     */
    public function subjectLibrary(Subject $subject)
    {
        $user = Auth::user();

        // التحقق من التسجيل في المادة
        if (!$subject->students()->where('users.id', $user->id)->exists()) {
            abort(403, 'يجب أن تكون مسجل في هذه المادة للوصول إلى مكتبتها.');
        }

        $filters = [
            'category_id' => request()->input('category_id'),
            'type' => request()->input('type'),
            'search' => request()->input('search'),
            'order_by' => request()->input('order_by', 'created_at'),
            'order_dir' => request()->input('order_dir', 'desc'),
            'per_page' => 20,
        ];

        $items = $this->libraryService->getSubjectItems($subject, $user, $filters);
        $categories = LibraryCategory::active()->ordered()->get();

        return view('student.pages.library.subject', compact('subject', 'items', 'categories'));
    }
}
