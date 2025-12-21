<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Badge;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    /**
     * عرض قائمة الإنجازات
     */
    public function index()
    {
        $achievements = Achievement::orderBy('order')->get();
        return view('admin.pages.achievements.index', compact('achievements'));
    }

    /**
     * عرض نموذج إنشاء إنجاز
     */
    public function create()
    {
        $badges = Badge::active()->get();
        return view('admin.pages.achievements.create', compact('badges'));
    }

    /**
     * حفظ إنجاز جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'type' => 'required|string|in:attendance,quiz,course,special,streak',
            'criteria' => 'nullable|array',
            'points_reward' => 'nullable|integer|min:0',
            'badge_id' => 'nullable|exists:badges,id',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        Achievement::create($request->all());

        return redirect()->route('admin.achievements.index')
            ->with('success', 'تم إنشاء الإنجاز بنجاح');
    }

    /**
     * عرض نموذج تعديل إنجاز
     */
    public function edit(Achievement $achievement)
    {
        $badges = Badge::active()->get();
        return view('admin.pages.achievements.edit', compact('achievement', 'badges'));
    }

    /**
     * تحديث إنجاز
     */
    public function update(Request $request, Achievement $achievement)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'type' => 'required|string|in:attendance,quiz,course,special,streak',
            'criteria' => 'nullable|array',
            'points_reward' => 'nullable|integer|min:0',
            'badge_id' => 'nullable|exists:badges,id',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $achievement->update($request->all());

        return redirect()->route('admin.achievements.index')
            ->with('success', 'تم تحديث الإنجاز بنجاح');
    }

    /**
     * حذف إنجاز
     */
    public function destroy(Achievement $achievement)
    {
        $achievement->delete();

        return redirect()->route('admin.achievements.index')
            ->with('success', 'تم حذف الإنجاز بنجاح');
    }
}

