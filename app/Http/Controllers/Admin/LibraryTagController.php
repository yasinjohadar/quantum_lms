<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LibraryTagController extends Controller
{
    /**
     * عرض قائمة الوسوم
     */
    public function index()
    {
        $tags = LibraryTag::withCount('items')
                         ->orderBy('name', 'asc')
                         ->paginate(20);

        return view('admin.pages.library.tags.index', compact('tags'));
    }

    /**
     * حفظ وسم جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:library_tags,name',
            'color' => 'nullable|string|max:7',
        ], [
            'name.required' => 'اسم الوسم مطلوب',
            'name.unique' => 'هذا الوسم موجود بالفعل',
        ]);

        try {
            LibraryTag::create($validated);

            return redirect()->route('admin.library.tags.index')
                           ->with('success', 'تم إنشاء الوسم بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating library tag: ' . $e->getMessage(), ['request' => $validated]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء الوسم.')
                           ->withInput();
        }
    }

    /**
     * حذف وسم
     */
    public function destroy(LibraryTag $tag)
    {
        try {
            $tag->delete();

            return redirect()->route('admin.library.tags.index')
                           ->with('success', 'تم حذف الوسم بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting library tag: ' . $e->getMessage(), ['tag_id' => $tag->id]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف الوسم.');
        }
    }
}
