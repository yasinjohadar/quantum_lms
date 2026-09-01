<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LibraryCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:library-category-list'])->only('index');
        $this->middleware(['permission:library-category-create'])->only(['create', 'store']);
        $this->middleware(['permission:library-category-edit'])->only(['edit', 'update']);
        $this->middleware(['permission:library-category-delete'])->only('destroy');
    }

    public function index()
    {
        $categories = LibraryCategory::withCount('items')->ordered()->paginate(20);

        return view('admin.pages.library.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.pages.library.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:library_categories,slug'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->has('is_active');

        try {
            LibraryCategory::create($data);

            return redirect()->route('admin.library.categories.index')
                ->with('success', 'تم إضافة التصنيف بنجاح');
        } catch (\Exception $e) {
            Log::error('Error creating library category: '.$e->getMessage());

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة التصنيف: '.$e->getMessage());
        }
    }

    public function edit(LibraryCategory $category)
    {
        return view('admin.pages.library.categories.edit', compact('category'));
    }

    public function update(Request $request, LibraryCategory $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:library_categories,slug,'.$category->id],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->has('is_active');

        try {
            $category->update($data);

            return redirect()->route('admin.library.categories.index')
                ->with('success', 'تم تحديث التصنيف بنجاح');
        } catch (\Exception $e) {
            Log::error('Error updating library category: '.$e->getMessage());

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث التصنيف: '.$e->getMessage());
        }
    }

    public function destroy(LibraryCategory $category)
    {
        if ($category->items()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف التصنيف لوجود عناصر مكتبة مرتبطة به.');
        }

        try {
            $category->delete();

            return redirect()->route('admin.library.categories.index')
                ->with('success', 'تم حذف التصنيف بنجاح');
        } catch (\Exception $e) {
            Log::error('Error deleting library category: '.$e->getMessage());

            return back()->with('error', 'حدث خطأ أثناء حذف التصنيف: '.$e->getMessage());
        }
    }
}
