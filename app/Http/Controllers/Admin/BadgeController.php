<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    /**
     * عرض قائمة الشارات
     */
    public function index()
    {
        $badges = Badge::orderBy('order')->get();
        return view('admin.pages.badges.index', compact('badges'));
    }

    /**
     * عرض نموذج إنشاء شارة
     */
    public function create()
    {
        return view('admin.pages.badges.create');
    }

    /**
     * حفظ شارة جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'points_required' => 'nullable|integer|min:0',
            'criteria' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'is_automatic' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        Badge::create($request->all());

        return redirect()->route('admin.badges.index')
            ->with('success', 'تم إنشاء الشارة بنجاح');
    }

    /**
     * عرض نموذج تعديل شارة
     */
    public function edit(Badge $badge)
    {
        return view('admin.pages.badges.edit', compact('badge'));
    }

    /**
     * تحديث شارة
     */
    public function update(Request $request, Badge $badge)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'points_required' => 'nullable|integer|min:0',
            'criteria' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'is_automatic' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $badge->update($request->all());

        return redirect()->route('admin.badges.index')
            ->with('success', 'تم تحديث الشارة بنجاح');
    }

    /**
     * حذف شارة
     */
    public function destroy(Badge $badge)
    {
        $badge->delete();

        return redirect()->route('admin.badges.index')
            ->with('success', 'تم حذف الشارة بنجاح');
    }
}

