<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    /**
     * عرض قائمة المستويات
     */
    public function index()
    {
        $levels = Level::orderBy('level_number')->get();
        return view('admin.pages.levels.index', compact('levels'));
    }

    /**
     * عرض نموذج إنشاء مستوى
     */
    public function create()
    {
        return view('admin.pages.levels.create');
    }

    /**
     * حفظ مستوى جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level_number' => 'required|integer|unique:levels,level_number',
            'points_required' => 'required|integer|min:0',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'benefits' => 'nullable|array',
            'order' => 'nullable|integer',
        ]);

        Level::create($request->all());

        return redirect()->route('admin.levels.index')
            ->with('success', 'تم إنشاء المستوى بنجاح');
    }

    /**
     * عرض نموذج تعديل مستوى
     */
    public function edit(Level $level)
    {
        return view('admin.pages.levels.edit', compact('level'));
    }

    /**
     * تحديث مستوى
     */
    public function update(Request $request, Level $level)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level_number' => 'required|integer|unique:levels,level_number,' . $level->id,
            'points_required' => 'required|integer|min:0',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'benefits' => 'nullable|array',
            'order' => 'nullable|integer',
        ]);

        $level->update($request->all());

        return redirect()->route('admin.levels.index')
            ->with('success', 'تم تحديث المستوى بنجاح');
    }

    /**
     * حذف مستوى
     */
    public function destroy(Level $level)
    {
        $level->delete();

        return redirect()->route('admin.levels.index')
            ->with('success', 'تم حذف المستوى بنجاح');
    }
}

