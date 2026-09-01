<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\LibraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LibraryItemController extends Controller
{
    /**
     * أعمدة مسموح بترتيب القائمة عليها — يمنع تمرير اسم عمود اعتباطي إلى orderBy().
     */
    private const ORDERABLE_COLUMNS = ['title', 'type', 'created_at', 'is_featured'];

    public function __construct(
        private LibraryService $libraryService
    ) {
        $this->middleware(['permission:library-item-list'])->only('index');
        $this->middleware(['permission:library-item-create'])->only(['create', 'store']);
        $this->middleware(['permission:library-item-edit'])->only(['edit', 'update']);
        $this->middleware(['permission:library-item-delete'])->only('destroy');
        $this->middleware(['permission:library-item-show'])->only('show');
        $this->middleware(['permission:library-item-download'])->only('download');
        $this->middleware(['permission:library-item-list'])->only('getSubjectsByClass');
    }

    public function index(Request $request)
    {
        $query = LibraryItem::query()->with(['category', 'subject', 'schoolClass', 'uploader']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->byCategory((int) $request->input('category_id'));
        }
        if ($request->filled('type')) {
            $query->byType($request->input('type'));
        }
        if ($request->filled('subject_id')) {
            $query->forSubject((int) $request->input('subject_id'));
        }
        if ($request->filled('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        $orderBy = in_array($request->input('order_by'), self::ORDERABLE_COLUMNS, true)
            ? $request->input('order_by')
            : 'created_at';
        $orderDir = $request->input('order_dir') === 'asc' ? 'asc' : 'desc';

        $items = $query->orderBy($orderBy, $orderDir)->paginate(20)->withQueryString();
        $categories = LibraryCategory::active()->ordered()->get();

        return view('admin.pages.library.items.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = LibraryCategory::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();

        return view('admin.pages.library.items.create', compact('categories', 'classes'));
    }

    public function store(Request $request)
    {
        $data = $this->validateItem($request);

        try {
            $item = $this->libraryService->createItem($data, $request->user());

            if ($request->hasFile('file')) {
                $this->libraryService->uploadFile($item, $request->file('file'));
            }

            return redirect()->route('admin.library.items.index')
                ->with('success', 'تم إضافة عنصر المكتبة بنجاح');
        } catch (\Exception $e) {
            Log::error('Error creating library item: '.$e->getMessage());

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة العنصر: '.$e->getMessage());
        }
    }

    public function show(LibraryItem $item)
    {
        $item->load(['category', 'subject', 'schoolClass', 'uploader']);

        return view('admin.pages.library.items.show', compact('item'));
    }

    public function edit(LibraryItem $item)
    {
        $categories = LibraryCategory::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        $subjects = $item->class_id
            ? Subject::active()->ordered()->where('class_id', $item->class_id)->get()
            : Subject::active()->ordered()->get();

        return view('admin.pages.library.items.edit', compact('item', 'categories', 'classes', 'subjects'));
    }

    public function update(Request $request, LibraryItem $item)
    {
        $data = $this->validateItem($request, $item->id);

        try {
            $this->libraryService->updateItem($item, $data);

            if ($request->hasFile('file')) {
                $this->libraryService->uploadFile($item, $request->file('file'));
            }

            return redirect()->route('admin.library.items.index')
                ->with('success', 'تم تحديث عنصر المكتبة بنجاح');
        } catch (\Exception $e) {
            Log::error('Error updating library item: '.$e->getMessage());

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث العنصر: '.$e->getMessage());
        }
    }

    public function destroy(LibraryItem $item)
    {
        try {
            $this->libraryService->deleteItem($item);

            return redirect()->route('admin.library.items.index')
                ->with('success', 'تم حذف عنصر المكتبة بنجاح');
        } catch (\Exception $e) {
            Log::error('Error deleting library item: '.$e->getMessage());

            return back()->with('error', 'حدث خطأ أثناء حذف العنصر: '.$e->getMessage());
        }
    }

    /**
     * تحميل العنصر من لوحة الإدارة — محكوم بصلاحية library-item-download فقط
     * (عبر middleware المُنشئ)، وليس بتسجيل الطالب في المادة/الصف كما في جهة الطالب.
     */
    public function download(LibraryItem $item)
    {
        if ($item->external_url) {
            return redirect()->away($item->external_url);
        }

        if (! $item->file_path) {
            abort(404, 'لا يوجد ملف لهذا العنصر');
        }

        return $this->libraryService->downloadResponse($item);
    }

    /**
     * مواد صف معيّن (AJAX) — لتغذية قائمة "المادة" المرتبطة بقائمة "الصف" في نموذج الإضافة/التعديل.
     */
    public function getSubjectsByClass(Request $request)
    {
        $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
        ]);

        $subjects = Subject::where('class_id', $request->input('class_id'))
            ->active()->ordered()->get(['id', 'name']);

        return response()->json(['success' => true, 'subjects' => $subjects]);
    }

    private function validateItem(Request $request, ?int $ignoreItemId = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:library_items,slug'.($ignoreItemId ? ",{$ignoreItemId}" : '')],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(LibraryItem::TYPES))],
            'category_id' => ['required', 'integer', 'exists:library_categories,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'file' => ['nullable', 'file', 'max:51200'],
            'external_url' => ['nullable', 'url', 'max:500'],
            'access_level' => ['required', 'string', 'in:'.implode(',', array_keys(LibraryItem::ACCESS_LEVELS))],
        ]);

        $hasExistingFile = $ignoreItemId && LibraryItem::whereKey($ignoreItemId)->whereNotNull('file_path')->exists();

        if ($data['type'] !== 'link' && ! $request->hasFile('file') && ! $hasExistingFile) {
            throw ValidationException::withMessages([
                'file' => 'يجب رفع ملف عندما لا يكون النوع رابطاً.',
            ]);
        }
        if ($data['type'] === 'link' && ! $request->filled('external_url') && ! $ignoreItemId) {
            throw ValidationException::withMessages([
                'external_url' => 'الرابط الخارجي مطلوب عندما يكون النوع رابطاً.',
            ]);
        }

        $data['is_featured'] = $request->has('is_featured');
        // العناصر المرتبطة بمادة تُتاح تلقائياً للطلاب المسجَّلين فيها بصرف النظر عن الخانة
        $data['is_public'] = $request->has('is_public') || $request->filled('subject_id');

        unset($data['file']);

        return $data;
    }
}
