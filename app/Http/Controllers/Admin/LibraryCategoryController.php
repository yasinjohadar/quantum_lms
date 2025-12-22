<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LibraryCategoryController extends Controller
{
    /**
     * عرض قائمة التصنيفات
     */
    public function index()
    {
        $categories = LibraryCategory::withCount('items')
                                    ->ordered()
                                    ->paginate(20);

        return view('admin.pages.library.categories.index', compact('categories'));
    }

    /**
     * عرض نموذج إنشاء تصنيف جديد
     */
    public function create()
    {
        return view('admin.pages.library.categories.create');
    }

    /**
     * حفظ تصنيف جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:library_categories,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'اسم التصنيف مطلوب',
            'slug.unique' => 'هذا الرابط مستخدم بالفعل',
        ]);

        try {
            $validated['is_active'] = $request->has('is_active');

            LibraryCategory::create($validated);

            return redirect()->route('admin.library.categories.index')
                           ->with('success', 'تم إنشاء التصنيف بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating library category: ' . $e->getMessage(), ['request' => $validated]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء التصنيف.')
                           ->withInput();
        }
    }

    /**
     * عرض تصنيف معين
     */
    public function show(LibraryCategory $category)
    {
        $category->load(['items' => function($query) {
            $query->latest()->limit(10);
        }]);

        return view('admin.pages.library.categories.show', compact('category'));
    }

    /**
     * عرض نموذج تعديل تصنيف
     */
    public function edit(LibraryCategory $category)
    {
        return view('admin.pages.library.categories.edit', compact('category'));
    }

    /**
     * تحديث تصنيف
     */
    public function update(Request $request, LibraryCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:library_categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'اسم التصنيف مطلوب',
            'slug.unique' => 'هذا الرابط مستخدم بالفعل',
        ]);

        try {
            $validated['is_active'] = $request->has('is_active');

            $category->update($validated);

            return redirect()->route('admin.library.categories.index')
                           ->with('success', 'تم تحديث التصنيف بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating library category: ' . $e->getMessage(), ['category_id' => $category->id, 'request' => $validated]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تحديث التصنيف.')
                           ->withInput();
        }
    }

    /**
     * حذف تصنيف
     */
    public function destroy(LibraryCategory $category)
    {
        try {
            // التحقق من وجود عناصر في التصنيف
            if ($category->items()->count() > 0) {
                return redirect()->back()
                               ->with('error', 'لا يمكن حذف التصنيف لأنه يحتوي على عناصر.');
            }

            $category->delete();

            return redirect()->route('admin.library.categories.index')
                           ->with('success', 'تم حذف التصنيف بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting library category: ' . $e->getMessage(), ['category_id' => $category->id]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف التصنيف.');
        }
    }
}
